<?php

declare(strict_types=1);

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentSuccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Psr\Log\LoggerInterface;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;

final class StripeProvider extends AbstractPaymentProvider
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->gatewayName = 'stripe';

        $apiKey = config('services.stripe.secret');

        if (! $apiKey) {
            throw new \RuntimeException('Stripe API key is not configured.');
        }

        Stripe::setApiKey($apiKey);
    }

    public function createPayment(array $data): array
    {
        $this->validatePaymentData($data);

        $params = [
            'amount' => (int) ($data['amount'] * 100), // Convert to cents
            'currency' => $data['currency'] ?? config('shop.default_currency', 'usd'),
            'metadata' => $this->sanitizeMetadata($data['metadata'] ?? []),
        ];

        // Add optional parameters with validation
        if (! empty($data['customer_id'])) {
            $params['customer'] = $this->validateStripeId($data['customer_id'], 'customer');
        }

        if (! empty($data['payment_method_id'])) {
            $params['payment_method'] = $this->validateStripeId($data['payment_method_id'], 'payment_method');
        }

        // Handle payment method type specific options
        if (! empty($data['payment_method_options'])) {
            $params['payment_method_options'] = $this->sanitizePaymentMethodOptions(
                $data['payment_method_options']
            );
        }

        try {
            $intent = PaymentIntent::create($params);

            $response = [
                'id' => $intent->id,
                'status' => $intent->status,
                'client_secret' => $intent->client_secret,
                'amount' => $intent->amount / 100, // Convert back from cents
                'currency' => $intent->currency,
            ];

            // Determine payment method type
            if (! empty($data['payment_method_id'])) {
                $paymentMethod = $this->retrievePaymentMethodSafely($data['payment_method_id']);
                $response['payment_method_type'] = $paymentMethod->type ?? null;
            }

            return $response;
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe payment creation failed', [
                'error' => $e->getMessage(),
                'data' => $this->sanitizeLogData($data),
            ]);

            throw new \RuntimeException('Payment creation failed. Please try again.', 0, $e);
        }
    }

    public function createCustomer(array $data): array
    {
        $customerData = [
            'email' => $this->validateEmail($data['email'] ?? ''),
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $this->sanitizeMetadata($data['metadata'] ?? []),
        ];

        // Add user_id to metadata for reference
        if (isset($data['user_id'])) {
            $customerData['metadata']['user_id'] = (string) $data['user_id'];
        }

        // Add phone if provided
        if (isset($data['mobile_number'])) {
            $customerData['phone'] = $this->validatePhone($data['mobile_number']);
        }

        // Add address if provided
        if (isset($data['addresses']) && is_array($data['addresses']) && ! empty($data['addresses'])) {
            $customerData['address'] = $this->sanitizeAddress($data['addresses'][0]);
        }

        try {
            $customer = Customer::create($customerData);

            return [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'name' => $customer->name,
                'metadata' => $customer->metadata?->toArray() ?? [],
            ];
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe customer creation failed', [
                'error' => $e->getMessage(),
                'data' => $this->sanitizeLogData($data),
            ]);

            throw new \RuntimeException('Customer creation failed. Please try again.', 0, $e);
        }
    }

    public function retrievePaymentMethod(string $methodKey, ?string $type = null): object
    {
        $this->validateStripeId($methodKey, 'payment_method');

        try {
            return StripePaymentMethod::retrieve($methodKey);
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe payment method retrieval failed', [
                'method_key' => $methodKey,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Payment method retrieval failed.', 0, $e);
        }
    }

    public function attachPaymentMethodToCustomer(string $methodKey, Authenticatable $user, ?string $type = null): object
    {
        $this->validateStripeId($methodKey, 'payment_method');

        try {
            $paymentMethod = StripePaymentMethod::retrieve($methodKey);

            // Attach to customer if customer exists
            if ($user->stripe_customer_id) {
                $paymentMethod->attach(['customer' => $user->stripe_customer_id]);
            }

            return $paymentMethod;
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe payment method attachment failed', [
                'method_key' => $methodKey,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Payment method attachment failed.', 0, $e);
        }
    }

    public function detachPaymentMethod(string $methodKey, ?string $type = null): void
    {
        $this->validateStripeId($methodKey, 'payment_method');

        try {
            $paymentMethod = StripePaymentMethod::retrieve($methodKey);
            $paymentMethod->detach();
        } catch (ApiErrorException $e) {
            $this->logger->warning('Stripe payment method detachment failed', [
                'method_key' => $methodKey,
                'error' => $e->getMessage(),
            ]);

            // Don't throw - allow graceful degradation
        }
    }

    public function initializePaymentMethod(array $data): ?array
    {
        try {
            $intent = SetupIntent::create([
                'customer' => $data['customer_id'] ?? null,
                'usage' => 'off_session',
                'metadata' => $this->sanitizeMetadata($data['metadata'] ?? []),
            ]);

            return [
                'client_secret' => $intent->client_secret,
                'id' => $intent->id,
                'status' => $intent->status,
            ];
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe setup intent creation failed', [
                'error' => $e->getMessage(),
                'data' => $this->sanitizeLogData($data),
            ]);

            return null;
        }
    }

    public function getSupportedPaymentMethods(): array
    {
        return [
            'card',
            'ideal',
            'giropay',
            'sofort',
            'bancontact',
            'alipay',
            'wechat_pay',
        ];
    }

    public function verifyPayment(string $transactionId): array
    {
        $this->validateStripeId($transactionId, 'payment_intent');

        try {
            $intent = PaymentIntent::retrieve($transactionId);

            return [
                'id' => $intent->id,
                'status' => $intent->status,
                'amount' => $intent->amount / 100,
                'currency' => $intent->currency,
                'payment_method_type' => $intent->payment_method_types[0] ?? null,
                'created' => $intent->created,
            ];
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Payment verification failed.', 0, $e);
        }
    }

    public function handleWebhook(object $request): void
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? null;

        // Log webhook for auditing
        $this->logger->info('Stripe webhook received', [
            'event_type' => $eventType,
            'event_id' => $payload['id'] ?? null,
        ]);

        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($payload['data']['object'] ?? []);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($payload['data']['object'] ?? []);
                break;
            case 'payment_intent.created':
                $this->handlePaymentCreated($payload['data']['object'] ?? []);
                break;
            default:
                $this->logger->debug('Unhandled Stripe webhook event', ['event_type' => $eventType]);
        }
    }

    private function handlePaymentSuccess(array $data): void
    {
        $orderTrackingNumber = $data['metadata']['order_tracking_number'] ?? null;

        if ($orderTrackingNumber) {
            $this->logger->info('Stripe payment success', [
                'order_tracking_number' => $orderTrackingNumber,
                'amount' => $data['amount'] / 100,
                'currency' => $data['currency'],
            ]);

            // Dispatch event for order status update
            event(new PaymentSuccess($orderTrackingNumber, $data));
        }
    }

    private function handlePaymentFailed(array $data): void
    {
        $orderTrackingNumber = $data['metadata']['order_tracking_number'] ?? null;

        if ($orderTrackingNumber) {
            $this->logger->warning('Stripe payment failed', [
                'order_tracking_number' => $orderTrackingNumber,
                'failure_message' => $data['last_payment_error']['message'] ?? 'Unknown error',
            ]);

            // Dispatch event for failed payment
            event(new PaymentFailed($orderTrackingNumber, $data));
        }
    }

    private function handlePaymentCreated(array $data): void
    {
        // Log payment creation for auditing
        $this->logger->debug('Stripe payment created', [
            'payment_intent_id' => $data['id'] ?? null,
            'amount' => $data['amount'] / 100,
        ]);
    }

    private function validateStripeId(string $id, string $type): string
    {
        if (! preg_match('/^[\w_]+$/', $id)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid %s ID format: %s', $type, $id)
            );
        }

        return $id;
    }

    private function validateEmail(string $email): string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        return $email;
    }

    private function validatePhone(string $phone): string
    {
        // Basic phone validation - can be enhanced based on requirements
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        if (strlen($cleaned) < 8) {
            throw new \InvalidArgumentException('Invalid phone number');
        }

        return $cleaned;
    }

    private function sanitizeMetadata(array $metadata): array
    {
        // Remove any sensitive data from metadata
        unset(
            $metadata['password'],
            $metadata['credit_card'],
            $metadata['cvv'],
            $metadata['ssn']
        );

        return $metadata;
    }

    private function sanitizeAddress(array $address): array
    {
        // Validate and sanitize address data
        $sanitized = [];

        $allowedFields = ['line1', 'line2', 'city', 'state', 'postal_code', 'country'];

        foreach ($allowedFields as $field) {
            if (isset($address[$field])) {
                $sanitized[$field] = substr((string) $address[$field], 0, 255);
            }
        }

        return $sanitized;
    }

    private function sanitizePaymentMethodOptions(array $options): array
    {
        // Only allow specific payment method options
        $allowedOptions = ['card' => ['cvc', 'installments']];

        $sanitized = [];

        foreach ($options as $method => $methodOptions) {
            if (isset($allowedOptions[$method]) && is_array($methodOptions)) {
                $sanitized[$method] = array_intersect_key(
                    $methodOptions,
                    array_flip($allowedOptions[$method])
                );
            }
        }

        return $sanitized;
    }

    private function sanitizeLogData(array $data): array
    {
        // Remove sensitive data from logs
        $sensitiveFields = [
            'cvv', 'cvc', 'card_number', 'credit_card',
            'password', 'token', 'secret', 'api_key',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    private function retrievePaymentMethodSafely(string $methodId): ?object
    {
        try {
            return StripePaymentMethod::retrieve($methodId);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to retrieve payment method for type detection', [
                'method_id' => $methodId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
