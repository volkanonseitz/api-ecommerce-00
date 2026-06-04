<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'shop_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'email_verified',
    ];

    public function getEmailVerifiedAttribute(): bool
    {
        return $this->hasVerifiedEmail();
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'customer_id');
    }

    public function address(): HasMany
    {
        return $this->hasMany(Address::class, 'customer_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'customer_id');
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    public function managed_shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function loadLastOrder()
    {
        $lastOrder = $this->orders()
            ->whereNull('parent_id')
            ->where('order_status', 'completed')
            ->latest()
            ->first();

        $this->setRelation('last_order', $lastOrder);

        return $this;
    }
}
