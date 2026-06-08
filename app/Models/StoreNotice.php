<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class StoreNotice extends Model
{
    use SoftDeletes;

    protected $table = 'store_notices';
    protected $guarded = [];
    protected $dates = ['effective_from', 'expired_at'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });
        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function read_status(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_notice_read')->withPivot('is_read');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_notice_user');
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'store_notice_shop');
    }

    // Helper methods untuk mendapatkan atribut yang sebelumnya ada di appends
    public function getCreatorRoleAttribute(): string
    {
        $permissions = $this->creator->permissions->pluck('name')->toArray();
        if (in_array(Permission::SUPER_ADMIN->value, $permissions)) {
            return ucfirst(str_replace('_', ' ', Permission::SUPER_ADMIN->value));
        }
        return ucfirst(str_replace('_', ' ', Permission::STORE_OWNER->value));
    }

    public function getIsReadAttribute(): bool
    {
        foreach ($this->read_status as $status) {
            if ($status->id === Auth::id() && $status->pivot->is_read) {
                return true;
            }
        }
        return false;
    }
}