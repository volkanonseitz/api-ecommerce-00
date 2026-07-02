<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case CARD = 'card';
    case VIRTUAL_ACCOUNT = 'virtual_account';
    case QRIS = 'qris';
    case E_WALLET = 'ewallet';
    case DIRECT_DEBIT = 'direct_debit';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case OVO = 'ovo';
    case DANA = 'dana';
    case GOPAY = 'gopay';
    case LINKAJA = 'linkaja';
    case SHOPEEPAY = 'shopeepay';

    public static function getGatewaySpecificTypes(string $gateway): array
    {
        return match ($gateway) {
            'stripe' => [
                self::CARD->value,
                'ideal',
                'giropay',
                'sofort',
                'bancontact',
                'alipay',
                'wechat_pay',
            ],
            'xendit' => [
                self::CARD->value,
                self::VIRTUAL_ACCOUNT->value,
                self::QRIS->value,
                self::E_WALLET->value,
                self::DIRECT_DEBIT->value,
                self::OVO->value,
                self::DANA->value,
                self::GOPAY->value,
                self::LINKAJA->value,
                self::SHOPEEPAY->value,
            ],
            'midtrans' => [
                self::CARD->value,
                self::VIRTUAL_ACCOUNT->value,
                self::QRIS->value,
                self::E_WALLET->value,
                self::BANK_TRANSFER->value,
                self::GOPAY->value,
                self::SHOPEEPAY->value,
            ],
            default => [self::CARD->value, self::VIRTUAL_ACCOUNT->value]
        };
    }

    public static function isCardBased(string $type): bool
    {
        return $type === self::CARD->value;
    }

    public static function isVA(string $type): bool
    {
        return $type === self::VIRTUAL_ACCOUNT->value;
    }

    public static function isQRIS(string $type): bool
    {
        return $type === self::QRIS->value;
    }

    public static function isEWallet(string $type): bool
    {
        return in_array($type, [
            self::E_WALLET->value,
            self::OVO->value,
            self::DANA->value,
            self::GOPAY->value,
            self::LINKAJA->value,
            self::SHOPEEPAY->value,
        ]);
    }

    public function label(): string
    {
        return match ($this) {
            self::CARD => 'Credit/Debit Card',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
            self::QRIS => 'QRIS',
            self::E_WALLET => 'E-Wallet',
            self::DIRECT_DEBIT => 'Direct Debit',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH => 'Cash',
            self::OVO => 'OVO',
            self::DANA => 'DANA',
            self::GOPAY => 'GoPay',
            self::LINKAJA => 'LinkAja',
            self::SHOPEEPAY => 'ShopeePay',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
