<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Models\Attachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttachmentService
{
    /**
     * @return LengthAwarePaginator<Attachment>
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Attachment::paginate($perPage);
    }

    public function findOrFail(int $id): Attachment
    {
        return Attachment::findOrFail($id);
    }

    public function delete(Attachment $attachment): void
    {
        // Hapus media juga (Spatie akan otomatis menghapus jika menggunakan `delete()`)
        $attachment->delete();
    }
}
