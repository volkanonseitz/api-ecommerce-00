<?php

declare(strict_types=1);

namespace App\Modules\PaymentMethod\DTO;

use App\Enums\PaymentMethodType;

final class PaymentMethodData
{
    public function __construct(
        public readonly string $method_key,
        public readonly string $method_type,
        public readonly bool $default_payment,
        public readonly string $payment_gateway,
        public readonly ?string $brand = null,
        public readonly ?string $last4 = null,
        public readonly ?string $exp_month = null,
        public readonly ?string $exp_year = null,
        public readonly ?string $va_number = null,
        public readonly ?string $bank_code = null,
        public readonly ?string $qris_url = null,
        public readonly ?string $ewallet_type = null,
        public readonly ?string $direct_debit_type = null,
        public readonly ?string $account_name = null,
        public readonly ?string $account_number = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            method_key: $data['method_key'],
            method_type: $data['method_type'] ?? PaymentMethodType::CARD->value,
            default_payment: $data['default_payment'] ?? false,
            payment_gateway: $data['payment_gateway'],
            brand: $data['brand'] ?? null,
            last4: $data['last4'] ?? null,
            exp_month: $data['exp_month'] ?? null,
            exp_year: $data['exp_year'] ?? null,
            va_number: $data['va_number'] ?? null,
            bank_code: $data['bank_code'] ?? null,
            qris_url: $data['qris_url'] ?? null,
            ewallet_type: $data['ewallet_type'] ?? null,
            direct_debit_type: $data['direct_debit_type'] ?? null,
            account_name: $data['account_name'] ?? null,
            account_number: $data['account_number'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    public static function fromCard(array $cardData, string $gateway): self
    {
        return new self(
            method_key: $cardData['id'] ?? $cardData['method_key'],
            method_type: PaymentMethodType::CARD->value,
            default_payment: false,
            payment_gateway: $gateway,
            brand: $cardData['brand'] ?? $cardData['card']['brand'] ?? null,
            last4: $cardData['last4'] ?? $cardData['card']['last4'] ?? null,
            exp_month: $cardData['exp_month'] ?? $cardData['card']['exp_month'] ?? null,
            exp_year: $cardData['exp_year'] ?? $cardData['card']['exp_year'] ?? null,
        );
    }

    public static function fromVirtualAccount(array $vaData, string $gateway): self
    {
        return new self(
            method_key: $vaData['id'] ?? $vaData['external_id'] ?? uniqid('va_'),
            method_type: PaymentMethodType::VIRTUAL_ACCOUNT->value,
            default_payment: false,
            payment_gateway: $gateway,
            va_number: $vaData['account_number'] ?? $vaData['va_number'] ?? null,
            bank_code: $vaData['bank_code'] ?? null,
            metadata: $vaData['metadata'] ?? [],
        );
    }

    public static function fromQRIS(array $qrisData, string $gateway): self
    {
        return new self(
            method_key: $qrisData['id'] ?? $qrisData['external_id'] ?? uniqid('qris_'),
            method_type: PaymentMethodType::QRIS->value,
            default_payment: false,
            payment_gateway: $gateway,
            qris_url: $qrisData['qr_code_url'] ?? $qrisData['qr_string'] ?? null,
            metadata: $qrisData['metadata'] ?? [],
        );
    }

    public static function fromEWallet(array $ewalletData, string $gateway): self
    {
        return new self(
            method_key: $ewalletData['id'] ?? $ewalletData['reference_id'] ?? uniqid('ewallet_'),
            method_type: PaymentMethodType::E_WALLET->value,
            default_payment: false,
            payment_gateway: $gateway,
            ewallet_type: $ewalletData['ewallet_type'] ?? $ewalletData['channel_code'] ?? null,
            metadata: $ewalletData['metadata'] ?? [],
        );
    }
}
