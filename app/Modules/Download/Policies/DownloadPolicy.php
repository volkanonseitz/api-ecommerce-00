<?php

declare(strict_types=1);

namespace App\Modules\Download\Policies;

use App\Models\OrderedFile;
use App\Models\User;

class DownloadPolicy
{
    public function viewAny(User $user): bool
    {
        // Semua user yang login bisa melihat daftar file yang bisa di-download
        return $user !== null;
    }

    public function view(User $user, OrderedFile $orderedFile): bool
    {
        // Hanya customer yang memiliki file ini
        return $user->id === $orderedFile->customer_id;
    }

    public function download(User $user, int $digitalFileId): bool
    {
        // Cek apakah user memiliki akses ke digital file ini
        return OrderedFile::where('digital_file_id', $digitalFileId)
            ->where('customer_id', $user->id)
            ->exists();
    }
}
