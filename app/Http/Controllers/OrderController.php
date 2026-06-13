<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\PaymentService;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\DTO\OrderData;
use App\Models\Order;
use App\Models\Settings;
use App\Models\DownloadToken;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderExport;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        $limit = $request->limit ?? 10;
        $orders = $this->orderService->getOrdersQuery($request, $user)->paginate($limit);
        return OrderResource::collection($orders);
    }

    public function store(OrderCreateRequest $request)
    {
        $settings = Settings::first();
        $data = OrderData::fromRequest($request->validated());
        $order = $this->orderService->createOrder($data, $settings, $request->user());
        return new OrderResource($order);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $order = $this->orderService->getOrderByTrackingOrId($params, $language, $request->user());
        // Attach payment intent jika perlu
        if (!in_array($order->payment_gateway, ['cash', 'cash_on_delivery', 'full_wallet_payment'])) {
            $order->payment_intent = $this->paymentService->attachPaymentIntent($order->tracking_number);
        }
        return new OrderResource($order);
    }

    public function findByTrackingNumber(Request $request, $tracking_number)
    {
        $order = $this->orderService->getOrderByTrackingOrId($tracking_number, $request->language ?? config('shop.default_language', 'id'), $request->user());
        return new OrderResource($order);
    }

    public function update(OrderUpdateRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $updated = $this->orderService->updateOrderStatus($order, $request->order_status, $request->user());
        return new OrderResource($updated);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['success' => true]);
    }

    public function exportOrderUrl(Request $request, $shop_id = null)
    {
        $user = $request->user();
        if (!$this->orderService->hasPermission($user, $request->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $url = $this->orderService->getExportToken($user->id, $request->shop_id);
        return response()->json(['url' => $url]);
    }

    public function exportOrder($token)
    {
        $downloadToken = DownloadToken::where('token', $token)->firstOrFail();
        $shopId = $downloadToken->payload;
        $downloadToken->delete();
        return Excel::download(new OrderExport($shopId), 'orders.xlsx');
    }

    public function downloadInvoiceUrl(Request $request)
    {
        $user = $request->user();
        if (!$this->orderService->hasPermission($user, $request->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $request->validate(['order_id' => 'required']);
        $language = $request->language ?? config('shop.default_language', 'id');
        $isRtl = $request->is_rtl ?? false;
        $translatedText = $request->translated_text ?? [];
        $url = $this->orderService->getInvoiceToken($user->id, $request->order_id, $language, $translatedText, $isRtl);
        return response()->json(['url' => $url]);
    }

    public function downloadInvoice($token)
    {
        $downloadToken = DownloadToken::where('token', $token)->firstOrFail();
        $payload = unserialize($downloadToken->payload);
        $downloadToken->delete();

        $order = Order::with(['products', 'children.shop', 'parent_order', 'wallet_point'])->where('id', $payload['order_id'])->orWhere('tracking_number', $payload['order_id'])->firstOrFail();
        $settings = Settings::getData($payload['language'] ?? config('shop.default_language', 'id'));
        $invoiceData = [
            'order' => $order,
            'settings' => $settings,
            'translated_text' => $payload['translated_text'],
            'is_rtl' => $payload['is_rtl'],
            'language' => $payload['language'],
        ];
        $pdf = Pdf::loadView('pdf.order-invoice', $invoiceData);
        return $pdf->download('invoice-order-' . $payload['order_id'] . '.pdf');
    }

    public function submitPayment(Request $request)
    {
        // Implementasi sesuai payment gateway, redirect ke payment processor
        // Karena ini kompleks, kita lewati sementara. Bisa panggil PaymentService.
        throw new \Exception('Not implemented in this refactor');
    }
}