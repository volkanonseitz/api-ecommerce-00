<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Policies;

use App\Enums\Permission;
use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        // Setiap user yang login bisa melihat daftar attachment miliknya atau semua (tergantung kebutuhan)
        return $user !== null;
    }

    public function view(User $user, Attachment $attachment): bool
    {
        // Attachment milik user?
        // Karena Attachment tidak memiliki foreign key user, kita asumsikan siapa pun bisa melihat
        // Atau bisa diterapkan berdasarkan media collection?
        // Untuk sekarang, semua bisa melihat (kecuali ada keperluan spesifik)
        return true;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        // Siapa pun bisa menghapus? Atau hanya admin?
        // Jika diperlukan, bisa dicek via permission
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }
}
