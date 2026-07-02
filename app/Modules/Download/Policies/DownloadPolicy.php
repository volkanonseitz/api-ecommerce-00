<?php

declare(strict_types=1);

namespace App\Modules\Download\Policies;

use App\Models\DigitalFile;
use App\Models\OrderedFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class DownloadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, OrderedFile $orderedFile): bool
    {
        return $user->id === $orderedFile->customer_id;
    }

    public function generateToken(User $user, OrderedFile $orderedFile): bool
    {
        return $user->id === $orderedFile->customer_id;
    }

    public function download(User $user, DigitalFile $digitalFile): bool
    {
        return $digitalFile->orderedFile && $digitalFile->orderedFile->customer_id === $user->id;
    }
}
