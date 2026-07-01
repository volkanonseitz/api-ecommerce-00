<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shop_id
 * @property float $admin_commission_rate
 * @property float $total_earnings
 * @property float $withdrawn_amount
 * @property float $current_balance
 * @property bool $is_custom_commission
 */
class Balance extends Model
{
    protected $table = 'balances';

    /**
     * SECURITY NOTE: Balance TIDAK PERNAH boleh diisi langsung dari input HTTP milik
     * Store Owner. Satu-satunya jalur legal untuk mengubah kolom ini adalah:
     * - ApproveShopAction (set admin_commission_rate awal, hanya SUPER_ADMIN)
     * - Proses internal order/withdraw settlement (di luar scope refactor ini)
     *
     * @var list<string>
     */
    protected $fillable = [
        'shop_id',
        'admin_commission_rate',
        'is_custom_commission',
        'payment_info',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admin_commission_rate' => 'float',
            'total_earnings' => 'float',
            'withdrawn_amount' => 'float',
            'current_balance' => 'float',
            'is_custom_commission' => 'boolean',
            'payment_info' => 'json',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
