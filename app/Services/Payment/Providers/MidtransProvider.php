<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentSuccess;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransProvider extends AbstractPaymentProvider
{
    public function __construct()
    {
        $this->gatewayName = 'midtrans';
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createPayment(array $data): array
    {
        $paymentMethodType = $data['payment_method_type'] ?? 'credit_card';

        switch ($paymentMethodType) {
            case 'credit_card':
                return $this->createCardPayment($data);
            case 'bank_transfer':
                return $this->createBankTransferPayment($data);
            case 'virtual_account':
                return $this->createVirtualAccountPayment($data);
            case 'qris':
                return $this->createQRISPayment($data);
            case 'ewallet':
                return $this->createEWalletPayment($data);
            default:
                return $this->createCardPayment($data);
        }
    }

    private function createCardPayment(array $data): array
    {
        $params = [
            'payment_type' => 'credit_card',
            'transaction_details' => [
                'order_id' => $data['order_tracking_number'] ?? uniqid('midtrans_'),
                'gross_amount' => $data['amount'],
            ],
            'customer_details' => [
                'email' => $data['email'] ?? $data['user_email'] ?? null,
                'first_name' => $data['name'] ?? null,
                'phone' => $data['mobile_number'] ?? null,
            ],
        ];

        if (! empty($data['payment_method_id'])) {
            $params['credit_card'] = ['token_id' => $data['payment_method_id']];
        }

        if (! empty($data['payment_method_options'])) {
            $params['credit_card'] = array_merge($params['credit_card'] ?? [], $data['payment_method_options']);
        }

        $response = CoreApi::charge($params);

        return [
            'id' => $response->transaction_id,
            'order_id' => $response->order_id,
            'status' => $response->transaction_status,
            'payment_method_type' => 'credit_card',
            'redirect_url' => $response->redirect_url ?? null,
            'payment_type' => $response->payment_type ?? null,
        ];
    }

    private function createBankTransferPayment(array $data): array
    {
        $bankCode = $data['payment_method_options']['bank_code'] ?? 'bca';

        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $data['order_tracking_number'] ?? uniqid('midtrans_'),
                'gross_amount' => $data['amount'],
            ],
            'customer_details' => [
                'email' => $data['email'] ?? null,
                'first_name' => $data['name'] ?? null,
                'phone' => $data['mobile_number'] ?? null,
            ],
            'bank_transfer' => [
                'bank' => $bankCode,
            ],
        ];

        $response = CoreApi::charge($params);

        return [
            'id' => $response->transaction_id,
            'order_id' => $response->order_id,
            'status' => $response->transaction_status,
            'payment_method_type' => 'bank_transfer',
            'va_number' => $response->va_numbers[0]->va_number ?? null,
            'bank_code' => $bankCode,
            'expiry_date' => $response->expiry_time ?? null,
        ];
    }

    private function createVirtualAccountPayment(array $data): array
    {
        return $this->createBankTransferPayment($data);
    }

    private function createQRISPayment(array $data): array
    {
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $data['order_tracking_number'] ?? uniqid('qris_'),
                'gross_amount' => $data['amount'],
            ],
            'customer_details' => [
                'email' => $data['email'] ?? null,
                'first_name' => $data['name'] ?? null,
                'phone' => $data['mobile_number'] ?? null,
            ],
        ];

        $response = CoreApi::charge($params);

        return [
            'id' => $response->transaction_id,
            'order_id' => $response->order_id,
            'status' => $response->transaction_status,
            'payment_method_type' => 'qris',
            'qr_code_url' => $response->actions[0]->url ?? null,
            'expiry_date' => $response->expiry_time ?? null,
        ];
    }

    private function createEWalletPayment(array $data): array
    {
        $ewalletType = $data['payment_method_options']['ewallet_type'] ?? 'gopay';

        $params = [
            'payment_type' => $ewalletType,
            'transaction_details' => [
                'order_id' => $data['order_tracking_number'] ?? uniqid('ewallet_'),
                'gross_amount' => $data['amount'],
            ],
            'customer_details' => [
                'email' => $data['email'] ?? null,
                'first_name' => $data['name'] ?? null,
                'phone' => $data['mobile_number'] ?? null,
            ],
        ];

        $response = CoreApi::charge($params);

        return [
            'id' => $response->transaction_id,
            'order_id' => $response->order_id,
            'status' => $response->transaction_status,
            'payment_method_type' => 'ewallet',
            'ewallet_type' => $ewalletType,
            'redirect_url' => $response->redirect_url ?? null,
            'actions' => $response->actions ?? [],
        ];
    }

    public function createCustomer(array $data): array
    {
        // Midtrans doesn't have customer creation API, we generate a reference
        return [
            'customer_id' => 'customer_'.($data['user_id'] ?? uniqid()),
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
        ];
    }

    public function retrievePaymentMethod(string $methodKey, ?string $type = null): object
    {
        // Midtrans doesn't have payment method retrieval API
        return (object) [
            'id' => $methodKey,
            'type' => $type ?? 'unknown',
            'gateway' => 'midtrans',
        ];
    }

    public function attachPaymentMethodToCustomer(string $methodKey, object $user, ?string $type = null): object
    {
        // Midtrans doesn't support payment method attachment
        return $this->retrievePaymentMethod($methodKey, $type);
    }

    public function detachPaymentMethod(string $methodKey, ?string $type = null): void
    {
        // Midtrans does not support payment method detachment
        \Log::info('Midtrans detach payment method not supported for key: '.$methodKey.' type: '.$type);
    }

    public function savePaymentMethod(object $paymentMethod, object $user, ?string $type = null): PaymentMethod
    {
        throw new \BadMethodCallException('MidtransProvider::savePaymentMethod should be implemented by child classes');
    }

    public function initializePaymentMethod(array $data): ?array
    {
        // Midtrans doesn't have setup intent, use Snap for payment initialization
        $params = [
            'transaction_details' => [
                'order_id' => $data['order_tracking_number'] ?? uniqid('setup_'),
                'gross_amount' => 1, // Minimum amount for setup
            ],
            'customer_details' => [
                'email' => $data['email'] ?? null,
                'first_name' => $data['name'] ?? null,
            ],
        ];

        try {
            $response = Snap::createTransaction($params);

            return [
                'token' => $response->token,
                'redirect_url' => $response->redirect_url,
            ];
        } catch (\Exception $e) {
            \Log::error('Midtrans setup intent failed: '.$e->getMessage());

            return null;
        }
    }

    public function getSupportedPaymentMethods(): array
    {
        return [
            'credit_card',
            'bank_transfer',
            'virtual_account',
            'qris',
            'gopay',
            'shopeepay',
        ];
    }

    public function verifyPayment(string $transactionId): array
    {
        // Midtrans has transaction status API
        $status = Transaction::status($transactionId);

        return [
            'id' => $status->transaction_id,
            'status' => $status->transaction_status,
            'amount' => $status->gross_amount,
            'currency' => 'IDR',
            'payment_method_type' => $status->payment_type,
        ];
    }

    public function handleWebhook(object $request): void
    {
        $payload = $request->all();
        $statusCode = $payload['status_code'] ?? null;

        if ($statusCode === '200' || $statusCode === '201') { // Midtrans can return 200 for success, 201 for pending (e.g., VA)
            $this->handleMidtransPaymentSuccess($payload);
        } else {
            $this->handleMidtransPaymentFailed($payload);
        }
    }

    protected function handleMidtransPaymentSuccess(array $data): void
    {
        $orderId = $data['order_id'] ?? null;
        if (! $orderId) {
            \Log::warning('Midtrans webhook success: order_id not found', ['payload' => $data]);

            return;
        }

        $order = Order::where('tracking_number', $orderId)->first();
        if ($order) {
            \Log::info('Midtrans payment success for order: '.$orderId);
            event(new PaymentSuccess($order, $data));
        } else {
            \Log::warning('Midtrans webhook success: Order not found for tracking number: '.$orderId, ['payload' => $data]);
        }
    }

    protected function handleMidtransPaymentFailed(array $data): void
    {
        $orderId = $data['order_id'] ?? null;
        if (! $orderId) {
            \Log::warning('Midtrans webhook failed: order_id not found', ['payload' => $data]);

            return;
        }

        $order = Order::where('tracking_number', $orderId)->first();
        if ($order) {
            \Log::warning('Midtrans payment failed for order: '.$orderId, ['payload' => $data]);
            event(new PaymentFailed($order, $data));
        } else {
            \Log::warning('Midtrans webhook failed: Order not found for tracking number: '.$orderId, ['payload' => $data]);
        }
    }
}
