<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $table = 'payment_methods';

    protected $fillable = [
        'payment_gateway_id',
        'method_key',
        'method_type',
        'fingerprint',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'default_payment',
        'provider_type',
        'provider_data',
        'metadata',
        'qris_url',
        'va_number',
        'bank_code',
        'ewallet_type',
        'direct_debit_type',
        'account_name',
        'account_number',
        'expiry_date',
    ];

    protected $casts = [
        'provider_data' => 'json',
        'metadata' => 'json',
        'default_payment' => 'boolean',
        'expiry_date' => 'datetime',
    ];

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function isCard(): bool
    {
        return $this->method_type === 'card';
    }

    public function isQRIS(): bool
    {
        return $this->method_type === 'qris';
    }

    public function isVirtualAccount(): bool
    {
        return $this->method_type === 'virtual_account';
    }

    public function isEWallet(): bool
    {
        return $this->method_type === 'ewallet';
    }

    public function isDirectDebit(): bool
    {
        return $this->method_type === 'direct_debit';
    }

    public function getDisplayName(): string
    {
        return match ($this->method_type) {
            'card' => $this->brand.' ****'.$this->last4,
            'virtual_account' => 'Virtual Account '.$this->bank_code.' '.$this->va_number,
            'qris' => 'QRIS',
            'ewallet' => ucfirst($this->ewallet_type).' Wallet',
            'direct_debit' => 'Direct Debit '.$this->account_last4,
            default => $this->method_type,
        };
    }
}
