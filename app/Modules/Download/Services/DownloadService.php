<?php

declare(strict_types=1);

namespace App\Modules\Download\Services;

use App\Models\DigitalFile;
use App\Models\DownloadToken;
use App\Models\OrderedFile;
use App\Modules\Download\Actions\GenerateDownloadTokenAction;
use App\Modules\Download\Actions\GetFileByTokenAction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadService
{
    public function __construct(
        private GenerateDownloadTokenAction $generateTokenAction,
        private GetFileByTokenAction $getFileByTokenAction,
    ) {}

    /**
     * Get query for downloadable files for a user
     *
     * @return Builder<OrderedFile>
     */
    public function getDownloadableFilesQuery(Authenticatable $user): Builder
    {
        return OrderedFile::where('customer_id', $user->id)
            ->with(['order']);
    }

    /**
     * Generate download token for a digital file
     */
    public function generateDownloadToken(int $digitalFileId, int $userId): DownloadToken
    {
        return $this->generateTokenAction->execute($digitalFileId, $userId);
    }

    /**
     * Get digital file by token and delete token
     */
    public function getFileByToken(string $token): ?DigitalFile
    {
        return $this->getFileByTokenAction->execute($token);
    }

    /**
     * Get media item by attachment_id (from Spatie MediaLibrary)
     */
    public function getMediaItem(int $attachmentId): ?Media
    {
        return Media::find($attachmentId);
    }
}
