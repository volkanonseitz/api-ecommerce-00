<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Enums\PaymentMethodType;

class XenditProvider extends AbstractPaymentProvider
{
    private $apiKey;
    private $apiBaseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->gatewayName = 'xendit';
        $this->apiKey = config('services.xendit.secret');
    }

    public function createPayment(array $data): array
    {
        $paymentMethodType = $data['payment_method_type'] ?? 'card';
        
        switch ($paymentMethodType) {
            case PaymentMethodType::CARD->value:
                return $this->createCardPayment($data);
            case PaymentMethodType::VIRTUAL_ACCOUNT->value:
                return $this->createVirtualAccountPayment($data);
            case PaymentMethodType::QRIS->value:
                return $this->createQRISPayment($data);
            case PaymentMethodType::E_WALLET->value:
                return $this->createEWalletPayment($data);
            case PaymentMethodType::DIRECT_DEBIT->value:
                return $this->createDirectDebitPayment($data);
            default:
                throw new \InvalidArgumentException("Unsupported payment method type: $paymentMethodType");
        }
    }

    private function createCardPayment(array $data): array
    {
        $payload = [
            'token_id' => $data['payment_method_id'],
            'external_id' => $data['order_tracking_number'] ?? uniqid('xendit_'),
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'IDR',
            'card_cvn' => $data['payment_method_options']['cvv'] ?? null,
        ];

        if (!empty($data['customer_id'])) {
            $payload['customer_id'] = $data['customer_id'];
        }

        // Call Xendit API
        $response = $this->makeRequest('/v2/charges', 'POST', $payload);

        return [
            'id' => $response['id'],
            'status' => $response['status'],
            'payment_method_type' => 'card',
            'authorization_url' => $response['authorization_url'] ?? null,
        ];
    }

    private function createVirtualAccountPayment(array $data): array
    {
        $payload = [
            'external_id' => $data['order_tracking_number'] ?? uniqid('va_'),
            'bank_code' => $data['payment_method_options']['bank_code'] ?? 'BCA',
            'name' => $data['name'] ?? 'Customer',
            'expected_amount' => $data['amount'],
            'is_closed' => true,
            'expiration_date' => date('c', strtotime('+24 hours')),
        ];

        if (!empty($data['customer_id'])) {
            $payload['customer_id'] = $data['customer_id'];
        }

        $response = $this->makeRequest('/callback_virtual_accounts', 'POST', $payload);

        return [
            'id' => $response['id'],
            'status' => 'pending',
            'payment_method_type' => 'virtual_account',
            'va_number' => $response['account_number'],
            'bank_code' => $response['bank_code'],
            'expiry_date' => $response['expiration_date'],
        ];
    }

    private function createQRISPayment(array $data): array
    {
        $payload = [
            'external_id' => $data['order_tracking_number'] ?? uniqid('qris_'),
            'amount' => $data['amount'],
            'type' => 'DYNAMIC',
            'callback_url' => config('app.url') . '/webhook/xendit/qris',
        ];

        $response = $this->makeRequest('/qr_codes', 'POST', $payload);

        return [
            'id' => $response['id'],
            'status' => 'pending',
            'payment_method_type' => 'qris',
            'qr_code_url' => $response['qr_code_url'] ?? $response['qr_string'],
            'expiry_date' => $response['expires_at'],
        ];
    }

    private function createEWalletPayment(array $data): array
    {
        $ewalletType = $data['payment_method_options']['ewallet_type'] ?? 'OVO';
        
        $payload = [
            'reference_id' => $data['order_tracking_number'] ?? uniqid('ewallet_'),
            'currency' => 'IDR',
            'amount' => $data['amount'],
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => $this->getEwalletChannelCode($ewalletType),
            'channel_properties' => [
                'mobile_number' => $data['payment_method_options']['mobile_number'] ?? null,
                'success_redirect_url' => config('app.url') . '/payment/success',
                'failure_redirect_url' => config('app.url') . '/payment/failed',
            ],
        ];

        if (!empty($data['customer_id'])) {
            $payload['customer_id'] = $data['customer_id'];
        }

        $response = $this->makeRequest('/ewallets/charges', 'POST', $payload);

        return [
            'id' => $response['id'],
            'status' => $response['status'],
            'payment_method_type' => 'ewallet',
            'checkout_url' => $response['actions']['mobile_deeplink_checkout_url'] ?? $response['actions']['desktop_web_checkout_url'] ?? null,
            'ewallet_type' => $ewalletType,
        ];
    }

    private function createDirectDebitPayment(array $data): array
    {
        // Xendit Direct Debit implementation
        $payload = [
            'reference_id' => $data['order_tracking_number'] ?? uniqid('dd_'),
            'currency' => 'IDR',
            'amount' => $data['amount'],
            'channel_code' => $data['payment_method_options']['bank_code'] ?? 'BCA',
            'payment_method_id' => $data['payment_method_id'],
        ];

        $response = $this->makeRequest('/direct_debits', 'POST', $payload);

        return [
            'id' => $response['id'],
            'status' => $response['status'],
            'payment_method_type' => 'direct_debit',
        ];
    }

    public function createCustomer(array $data): array
    {
        $payload = [
            'reference_id' => $data['reference_id'] ?? 'cust_' . uniqid(),
            'email' => $data['email'],
            'given_names' => $data['name'] ?? '',
            'mobile_number' => $data['mobile_number'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ];

        // Xendit specific customer fields
        if (isset($data['nationality'])) {
            $payload['nationality'] = $data['nationality'];
        }

        if (isset($data['id_number'])) {
            $payload['identity_account_number'] = $data['id_number'];
        }

        $response = $this->makeRequest('/customers', 'POST', $payload);

        return [
            'customer_id' => $response['id'],
            'reference_id' => $response['reference_id'],
            'email' => $response['email'],
            'name' => $response['given_names'],
            'metadata' => $response['metadata'] ?? [],
        ];
    }

    public function handleWebhook(object $request): void
    {
        $payload = $request->all();
        $eventType = $payload['event'] ?? null;

        switch ($eventType) {
            case 'payment.succeeded':
                $this->handlePaymentSuccess($payload['data']);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($payload['data']);
                break;
            case 'payment.expired':
                $this->handlePaymentExpired($payload['data']);
                break;
            default:
                \Log::info('Xendit webhook received: ' . $eventType);
        }
    }

    protected function handlePaymentSuccess(array $data): void
    {
        $externalId = $data['external_id'] ?? null;
        if ($externalId) {
            \Log::info('Xendit payment success for: ' . $externalId);
            // Update payment status logic here
        }
    }

    protected function handlePaymentFailed(array $data): void
    {
        $externalId = $data['external_id'] ?? null;
        if ($externalId) {
            \Log::warning('Xendit payment failed for: ' . $externalId);
        }
    }

    protected function handlePaymentExpired(array $data): void
    {
        $externalId = $data['external_id'] ?? null;
        if ($externalId) {
            \Log::warning('Xendit payment expired for: ' . $externalId);
        }
    }

    public function getSupportedPaymentMethods(): array
    {
        return PaymentMethodType::getGatewaySpecificTypes('xendit');
    }

    public function verifyPayment(string $transactionId): array
    {
        $response = $this->makeRequest("/v2/charges/$transactionId", 'GET');

        return [
            'id' => $response['id'],
            'status' => $response['status'],
            'amount' => $response['amount'],
            'currency' => $response['currency'],
            'payment_method_type' => $response['payment_method'] ?? null,
        ];
    }

    private function makeRequest(string $endpoint, string $method, array $data = []): array
    {
        $url = $this->apiBaseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = [
            'Authorization: Basic ' . base64_encode($this->apiKey . ':'),
            'Content-Type: application/json',
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \RuntimeException("Xendit API error: HTTP $httpCode - $response");
        }

        return json_decode($response, true);
    }

    private function getEwalletChannelCode(string $ewalletType): string
    {
        return match(strtoupper($ewalletType)) {
            'OVO' => 'ID_OVO',
            'DANA' => 'ID_DANA',
            'LINKAJA' => 'ID_LINKAJA',
            'SHOPEEPAY' => 'ID_SHOPEEPAY',
            'GOPAY' => 'ID_GOPAY',
            default => 'ID_OVO',
        };
    }
}