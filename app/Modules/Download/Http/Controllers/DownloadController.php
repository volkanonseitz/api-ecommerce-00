<?php

declare(strict_types=1);

namespace App\Modules\Download\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\OrderedFile;
use App\Modules\Download\Http\Requests\GenerateDownloadUrlRequest;
use App\Modules\Download\Http\Resources\DownloadableFileResource;
use App\Modules\Download\Services\DownloadService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadController extends BaseController
{
    public function __construct(private DownloadService $downloadService) {}

    public function fetchDownloadableFiles(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', OrderedFile::class);

        $limit = (int) ($request->limit ?? 15);
        $query = $this->downloadService->getDownloadableFilesQuery($user);

        // Load morph relations: file.fileable (product/variation with shop)
        $query->with(['file.fileable' => function ($q) {
            $q->with('shop');
        }]);

        $files = $query->paginate($limit);

        return DownloadableFileResource::collection($files);
    }

    public function generateDownloadableUrl(GenerateDownloadUrlRequest $request)
    {
        $user = $request->user();
        $digitalFileId = $request->digital_file_id;

        $this->authorize('download', [OrderedFile::class, $digitalFileId]);

        $token = $this->downloadService->generateDownloadToken($digitalFileId, $user->id);

        return response()->json([
            'url' => route('download_url.token', ['token' => $token->token]),
        ]);
    }

    public function downloadFile(string $token)
    {
        $digitalFile = $this->downloadService->getFileByToken($token);
        if (! $digitalFile) {
            throw new HttpException(404, config('notice.TOKEN_NOT_FOUND', 'Token not found'));
        }

        $mediaItem = $this->downloadService->getMediaItem($digitalFile->attachment_id);
        if (! $mediaItem) {
            throw new HttpException(404, config('notice.NOT_FOUND', 'File not found'));
        }

        // Return file download response (Spatie MediaLibrary)
        return $mediaItem;
    }
}
