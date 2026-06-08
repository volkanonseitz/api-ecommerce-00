<?php

namespace App\Services;

use App\Models\OrderedFile;
use App\Models\DownloadToken;
use App\Models\User;
use App\Models\DigitalFile;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadService
{
    /**
     * Get query for downloadable files for a user
     */
    public function getDownloadableFilesQuery(Authenticatable $user): Builder
    {
        return OrderedFile::where('customer_id', $user->id)
            ->with(['order']);
    }

    /**
     * Check if user has access to a digital file
     */
    public function userHasAccessToFile(Authenticatable $user, int $digitalFileId): bool
    {
        return OrderedFile::where('digital_file_id', $digitalFileId)
            ->where('customer_id', $user->id)
            ->exists();
    }

    /**
     * Generate download token for a digital file
     */
    public function generateDownloadToken(int $digitalFileId, int $userId): DownloadToken
    {
        return DownloadToken::create([
            'user_id' => $userId,
            'token' => Str::random(16),
            'digital_file_id' => $digitalFileId,
        ]);
    }

    /**
     * Get digital file by token and delete token
     */
    public function getFileByToken(string $token): ?DigitalFile
    {
        $downloadToken = DownloadToken::with('file')->where('token', $token)->first();
        if (!$downloadToken) {
            return null;
        }
        $digitalFile = $downloadToken->file;
        $downloadToken->delete();
        return $digitalFile;
    }

    /**
     * Get media item by attachment_id (from Spatie MediaLibrary)
     */
    public function getMediaItem(int $attachmentId): ?Media
    {
        return Media::find($attachmentId);
    }
}