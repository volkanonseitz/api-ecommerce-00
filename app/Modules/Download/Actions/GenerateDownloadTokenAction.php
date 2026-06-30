<?php

declare(strict_types=1);

namespace App\Modules\Download\Actions;

use App\Models\DownloadToken;
use Illuminate\Support\Str;

class GenerateDownloadTokenAction
{
    public function execute(int $digitalFileId, int $userId): DownloadToken
    {
        return DownloadToken::create([
            'user_id' => $userId,
            'token' => Str::random(16),
            'digital_file_id' => $digitalFileId,
        ]);
    }
}
