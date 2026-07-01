<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Auth\Authenticatable;

interface PaymentProviderInterface
{
    /**
     * Create a payment transaction.
     * @param array{
     *     amount: float,
     *     currency: string,
     *     order_tracking_number?: string,
     *     customer_id?: string,
     *     payment_method_id?: string,
     *     payment_method_type?: string,
     *     email?: string,
     *     name?: string,
     *     description?: string,
     *     metadata?: array,
     *     payment_method_options?: array,
     *     billing_address?: array,
     *     shipping_address?: array,
     *     items?: array
     * } $data
     * @return array{
     *     id: string,
     *     status: string,
     *     payment_method_type?: string,
     *     payment_method?: mixed,
     *     actions?: array,
     *     redirect_url?: string,
     *     checkout_url?: string,
     *     authorization_url?: string,
     *     client_secret?: string,
     *     qr_code_url?: string,
     *     va_number?: string,
     *     bank_code?: string,
     *     expiry_date?: string
     * }
     */
    public function createPayment(array $data): array;

    /**
     * Create a customer in the gateway.
     * @param array{
     *     user_id?: int,
     *     email: string,
     *     name?: string,
     *     reference_id?: string,
     *     mobile_number?: string,
     *     addresses?: array,
     *     metadata?: array,
     *     nationality?: string,
     *     id_number?: string,
     *     description?: string,
     *     payment_methods?: array
     * } $data
     * @return array{
     *     customer_id: string,
     *     reference_id?: string,
     *     email?: string,
     *     name?: string,
     *     metadata?: array
     * }
     */
    public function createCustomer(array $data): array;

    /**
     * Retrieve a payment method by its identifier.
     * @param string $methodKey
     * @param string|null $type
     * @return object
     */
    public function retrievePaymentMethod(string $methodKey, ?string $type = null): object;

    /**
     * Attach payment method to a customer (if supported).
     * @param string $methodKey
     * @param Authenticatable $user
     * @param string|null $type
     * @return object
     */
    public function attachPaymentMethodToCustomer(string $methodKey, Authenticatable $user, ?string $type = null): object;

    /**
     * Detach payment method from customer (if supported).
     * @param string $methodKey
     * @param string|null $type
     * @return void
     */
    public function detachPaymentMethod(string $methodKey, ?string $type = null): void;

    /**
     * Save a payment method to local database.
     * @param object $paymentMethodData
     * @param Authenticatable $user
     * @param string|null $type
     * @return PaymentMethod
     */
    public function savePaymentMethod(object $paymentMethodData, Authenticatable $user, ?string $type = null): PaymentMethod;

    /**
     * Create a payment initialization for adding a new payment method.
     * For Stripe: Setup Intent, for others: Payment initialization
     * @param array $data
     * @return array|null
     */
    public function initializePaymentMethod(array $data): ?array;

    /**
     * Handle webhook from gateway.
     * @param object $request
     * @return void
     */
    public function handleWebhook(object $request): void;

    /**
     * Get the gateway name.
     * @return string
     */
    public function getGatewayName(): string;

    /**
     * Get supported payment method types.
     * @return array<string>
     */
    public function getSupportedPaymentMethods(): array;

    /**
     * Verify a payment transaction.
     * @param string $transactionId
     * @return array{
     *     id: string,
     *     status: string,
     *     amount: float,
     *     currency: string,
     *     payment_method_type?: string
     * }
     */
    public function verifyPayment(string $transactionId): array;
}