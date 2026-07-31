<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleHasPermission extends Pivot
{
    use HasFactory;

    protected $table = 'role_has_permissions';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_id',
    ];
}
