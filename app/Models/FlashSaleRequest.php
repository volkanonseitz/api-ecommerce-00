<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlashSaleRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'flash_sale_requests';

    protected $guarded = [];

    protected $casts = [
        'request_status' => 'boolean',
        'requested_product_ids' => 'array',
    ];

    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class, 'flash_sale_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_requests_products', 'flash_sale_requests_id', 'product_id');
    }
}
