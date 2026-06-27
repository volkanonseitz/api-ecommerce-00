<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Order\Actions\OrderManagementAction;
use App\Domains\Order\DTO\OrderData;
use App\Domains\Order\Services\OrderIdentityService;
use App\Domains\Order\Services\OrderService;
use App\Enums\Permission;
use App\Exports\OrderExport;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\DownloadToken;
use App\Models\Order;
use App\Models\Settings;
use App\Services\AddressFormatterService;
use App\Services\CurrencyFormatterService;
use App\Services\PaymentService;
use App\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService,
        private OrderIdentityService $identityService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $limit = $request->limit ?? 10;
        $orders = $this->orderService->getOrdersQuery($request, $user)->paginate((int) $limit);

        return OrderResource::collection($orders);
    }

    /**
     * Menyimpan pesanan baru (Checkout) menggunakan OrderManagementAction.
     */
    public function store(OrderCreateRequest $request, OrderManagementAction $orderManagementAction)
    {
        $settings = Settings::first();
        $data = OrderData::fromRequest($request->validated());

        // Memanggil Action orkestrasi logika checkout yang baru
        $order = $orderManagementAction->execute($data, $settings, $request->user());

        return new OrderResource($order);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $order = $this->orderService->getOrderByTrackingOrId($params, $language, $request->user());

        if (! in_array($order->payment_gateway, ['cash', 'cash_on_delivery', 'full_wallet_payment'], true)) {
            $order->payment_intent = $this->paymentService->attachPaymentIntent($order->tracking_number);
        }

        return new OrderResource($order);
    }

    public function findByTrackingNumber(Request $request, $tracking_number)
    {
        $order = $this->orderService->getOrderByTrackingOrId(
            $tracking_number,
            $request->language ?? config('shop.default_language', 'id'),
            $request->user()
        );

        return new OrderResource($order);
    }

    public function update(OrderUpdateRequest $request, int $id)
    {
        $order = Order::findOrFail($id);
        $user = $request->user();

        if (! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            && ! $this->orderService->hasPermission($user, $order->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $updated = $this->orderService->updateOrderStatus($order, $request->order_status, $user);

        return $this->sendSuccess(new OrderResource($updated), 'Order updated');
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $order = Order::findOrFail($id);

        if (! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            && ! $this->orderService->hasPermission($user, $order->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $order->delete();

        return $this->sendSuccess(null, 'Order deleted');
    }

    public function exportOrderUrl(Request $request, $shop_id = null)
    {
        $user = $request->user();
        if (! $this->orderService->hasPermission($user, $request->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        // Diarahkan ke OrderIdentityService
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
        $user = $request->user();
        if (! $this->orderService->hasPermission($user, $request->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $request->validate(['order_id' => 'required']);
        $language = $request->language ?? config('shop.default_language', 'id');
        $isRtl = $request->is_rtl ?? false;
        $translatedText = $request->translated_text ?? [];

        // Menggunakan getInvoiceTokenSecure dari OrderIdentityService yang sudah aman dari Object Insecure Deserialization
        $url = $this->identityService->getInvoiceTokenSecure(
            $user->id, (int) $request->order_id, $language, $translatedText, (bool) $isRtl
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

    public function submitPayment(Request $request)
    {
        throw new \Exception('Not implemented in this refactor');
    }
}
