<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $slug
 * @property bool $is_active
 */
class Shop extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'shops';

    /**
     * SECURITY FIX: sebelumnya `protected $guarded = []` yang membuat SEMUA kolom
     * bisa diisi lewat mass assignment, termasuk kolom sensitif yang seharusnya
     * hanya bisa diubah lewat Action/flow tertentu (mis. `is_active` hanya lewat
     * ApproveShopAction, bukan lewat create()/update() biasa).
     *
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'logo',
        'address',
        'settings',
        'notifications',
    ];

    /**
     * `is_active` SENGAJA tidak dimasukkan ke $fillable. Status aktif toko
     * hanya boleh diubah lewat ApproveShopAction / DisapproveShopAction /
     * EnableShopMaintenanceAction, agar tidak ada jalur lain (mis. lewat
     * UpdateShopAction) yang bisa mengaktifkan toko tanpa approval SUPER_ADMIN.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logo' => 'json',
            'cover_image' => 'json',
            'address' => 'json',
            'settings' => 'json',
            'notifications' => 'json', // tambahkan ini
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = self::generateUniqueSlug((string) $model->name);
            }
        });

        static::updating(function (self $model): void {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = self::generateUniqueSlug((string) $model->name, $model->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);

        $query = self::where('slug', $slug);
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        $count = $query->count();

        return $count > 0 ? $slug.'-'.($count + 1) : $slug;
    }

    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class, 'shop_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(User::class, 'shop_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_shop');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_shop');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shop_id');
    }

    public function ownershipHistory(): HasOne
    {
        return $this->hasOne(OwnershipTransfer::class, 'shop_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'owner_id' => $this->owner_id,
            'address' => $this->address,
        ];
    }

    /**
     * Default commission rate. Logika bisa dikembangkan (mis. tiered by total earnings),
     * tapi TIDAK PERNAH boleh menerima nilai dari user input mentah tanpa melalui
     * ApproveShopAction yang sudah tervalidasi lewat FormRequest & Policy SUPER_ADMIN.
     */
    public function getDefaultCommissionRate(float $totalEarnings): float
    {
        return 10.0;
    }
}
