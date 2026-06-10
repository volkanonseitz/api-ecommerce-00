<?php

namespace App\Services\Payment;

use Exception;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Traits\PaymentTrait; // jika masih pakai trait, atau pindah ke service
use KingFlamez\Rave\Facades\Rave as FlutterwaveFacade;

class FlutterwaveProvider implements PaymentProviderInterface
{
    use PaymentTrait; // sementara, nanti bisa dipisah

    protected FlutterwaveFacade $flutterwave;
    protected string $currency;

    public function __construct()
    {
        $this->currency = config('shop.default_currency', 'USD');
        $this->flutterwave = new FlutterwaveFacade(
            config('services.flutterwave.secret_key'),
            config('services.flutterwave.public_key')
        );
    }

    public function createIntent(array $data): array
    {
        try {
            $reference = FlutterwaveFacade::generateReference();
            $paymentData = [
                'payment_options' => 'card,banktransfer',
                'amount' => number_format($data['amount'], 2),
                'email' => $data['user_email'] ?? $data['order_tracking_number'] . '@email.com',
                'tx_ref' => $reference,
                'currency' => $this->currency,
                'redirect_url' => route('callback.flutterwave'),
                'meta' => ['order_tracking_number' => $data['order_tracking_number']],
                'customer' => ['email' => $data['user_email'] ?? $data['order_tracking_number'] . '@email.com'],
            ];
            $order = FlutterwaveFacade::initializePayment($paymentData);
            return [
                'order_tracking_number' => $data['order_tracking_number'],
                'is_redirect' => true,
                'payment_id' => $paymentData['tx_ref'],
                'tx_ref_id' => $paymentData['tx_ref'],
                'redirect_url' => $order['data']['link'],
            ];
        } catch (Exception $e) {
            throw new \Exception(config('constants.SOMETHING_WENT_WRONG_WITH_PAYMENT'));
        }
    }

    public function createCustomer(array $data): array
    {
        return [];
    }

    public function handleWebhook(Request $request): void
    {
        try {
            $verified = FlutterwaveFacade::verifyWebhook();
            if ($verified && $request->event == 'charge.completed' && $request->data['status'] == 'successful') {
                $verificationData = FlutterwaveFacade::verifyTransaction($request->data['id']);
                if ($verificationData['status'] === 'success') {
                    $this->updatePaymentOrderStatus($request, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
                }
            }
        } catch (Exception $e) {
            throw new \Exception(config('constants.SOMETHING_WENT_WRONG_WITH_PAYMENT'));
        }
    }

    protected function updatePaymentOrderStatus($request, $orderStatus, $paymentStatus): void
    {
        $paymentIntent = PaymentIntent::whereJsonContains('payment_intent_info', ['tx_ref_id' => $request['data']['tx_ref']])->first();
        if (!$paymentIntent) return;
        $order = Order::where('tracking_number', $paymentIntent->tracking_number)->first();
        if ($order) {
            $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
        }
    }

    // Untuk callback (bukan webhook)
    public function handleCallback(Request $request)
    {
        try {
            $tx_ref = $request['tx_ref'];
            if ($request['status'] == 'cancelled') {
                $paymentIntent = PaymentIntent::whereJsonContains('payment_intent_info->payment_id', $tx_ref)->first();
                if ($paymentIntent) {
                    return redirect(config('shop.shop_url') . "/orders/{$paymentIntent->payment_intent_info['order_tracking_number']}/payment");
                }
                return redirect(config('shop.shop_url'));
            }

            $transactionID = $request['transaction_id'];
            $result = FlutterwaveFacade::verifyTransaction($transactionID);
            $trackingNumber = $result['data']['meta']['order_tracking_number'];
            PaymentIntent::whereJsonContains('payment_intent_info->payment_id', $tx_ref)->update([
                'payment_intent_info->payment_id' => $transactionID
            ]);
            return redirect(config('shop.shop_url') . "/orders/{$trackingNumber}/thank-you");
        } catch (Exception $e) {
            throw new \Exception(config('constants.SOMETHING_WENT_WRONG_WITH_PAYMENT'));
        }
    }
}