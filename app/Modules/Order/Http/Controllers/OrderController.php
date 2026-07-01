<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Exports\OrderExport;
use App\Http\Controllers\BaseController;
use App\Models\DownloadToken;
use App\Models\Order;
use App\Models\Settings;
use App\Modules\Address\Services\AddressFormatterService;
use App\Modules\Order\Actions\CreateOrderAction;
use App\Modules\Order\Actions\UpdateOrderStatusAction;
use App\Modules\Order\DTO\OrderData;
use App\Modules\Order\Http\Requests\CreateOrderRequest;
use App\Modules\Order\Http\Requests\UpdateOrderRequest;
use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\Order\Services\OrderIdentityService;
use App\Modules\Order\Services\OrderService;
use App\Services\CurrencyFormatterService;
use App\Services\Payment\PaymentService;
use App\Modules\Settings\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $orderService,
        private OrderIdentityService $identityService,
        private PaymentService $paymentService,
        private CreateOrderAction $createOrderAction,
        private UpdateOrderStatusAction $updateOrderStatusAction,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);
        $user = $request->user();
        $limit = (int) ($request->limit ?? 10);
        $orders = $this->orderService->getOrdersQuery($request, $user)->paginate($limit);

        return OrderResource::collection($orders);
    }

    public function store(CreateOrderRequest $request)
    {
        $this->authorize('create', Order::class);
        $settings = Settings::first();
        $data = OrderData::fromRequest($request->validated());
        $order = $this->createOrderAction->execute($data, $settings, $request->user());

        return new OrderResource($order);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $order = $this->orderService->getOrderByTrackingOrId($params, $language, $request->user());
        $this->authorize('view', $order);

        if (! in_array($order->payment_gateway, ['cash', 'cash_on_delivery', 'full_wallet_payment'], true)) {
            $order->payment_intent = $this->paymentService->attachPaymentIntent($order->tracking_number);
        }

        return new OrderResource($order);
    }

    public function update(UpdateOrderRequest $request, int $id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);
        $updated = $this->updateOrderStatusAction->execute($order, $request->order_status);

        return $this->sendSuccess(new OrderResource($updated), 'Order updated');
    }

    public function destroy(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);
        $order->delete();

        return $this->sendSuccess(null, 'Order deleted');
    }

    public function exportOrderUrl(Request $request, $shop_id = null)
    {
        $this->authorize('export', [Order::class, $request->shop_id]);
        $user = $request->user();
        $url = $this->identityService->getExportToken($user->id, $request->shop_id ? (int) $request->shop_id : null);

        return response()->json(['url' => $url]);
    }

    public function exportOrder(Request $request, $token)
    {
        $downloadToken = DownloadToken::where('token', $token)
            ->where('user_id', $request->user()?->id)
            ->firstOrFail();

        $shopId = json_decode($downloadToken->payload, true);
        $downloadToken->delete();

        $query = Order::with(['customer', 'shop']);
        if ($shopId) {
            $query->where('shop_id', $shopId);
        } else {
            $query->whereNull('parent_id');
        }
        $orders = $query->get();

        $addressFormatter = app(AddressFormatterService::class);
        $currencyFormatter = app(CurrencyFormatterService::class);
        $settingsService = app(SettingsService::class);

        return Excel::download(
            new OrderExport($orders, $shopId, $addressFormatter, $currencyFormatter, $settingsService),
            'orders.xlsx'
        );
    }

    public function downloadInvoiceUrl(Request $request)
    {
        $this->authorize('export', [Order::class, $request->shop_id]);
        $request->validate(['order_id' => 'required|integer']);
        $user = $request->user();
        $language = $request->language ?? config('shop.default_language', 'id');
        $isRtl = $request->is_rtl ?? false;
        $translatedText = $request->translated_text ?? [];

        $url = $this->identityService->getInvoiceTokenSecure(
            $user->id,
            (int) $request->order_id,
            $language,
            $translatedText,
            (bool) $isRtl
        );

        return response()->json(['url' => $url]);
    }

    public function downloadInvoice(Request $request, $token)
    {
        $downloadToken = DownloadToken::where('token', $token)
            ->where('user_id', $request->user()?->id)
            ->firstOrFail();

        $payload = json_decode($downloadToken->payload, true);
        $downloadToken->delete();

        $order = Order::with(['products', 'children.shop', 'parent_order', 'wallet_point'])
            ->where('id', $payload['order_id'])
            ->orWhere('tracking_number', $payload['order_id'])
            ->firstOrFail();

        $settings = Settings::getData($payload['language'] ?? config('shop.default_language', 'id'));
        $invoiceData = [
            'order' => $order,
            'settings' => $settings,
            'translated_text' => $payload['translated_text'],
            'is_rtl' => $payload['is_rtl'],
            'language' => $payload['language'],
        ];
        $pdf = Pdf::loadView('pdf.order-invoice', $invoiceData);

        return $pdf->download('invoice-order-'.$payload['order_id'].'.pdf');
    }
}
