<?php

namespace App\Http\Controllers;

use App\Services\DownloadService;
use App\Http\Resources\DownloadableFileResource;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadController extends Controller
{
    public function __construct(private DownloadService $downloadService) {}

    /**
     * GET /downloadable-files
     */
    public function fetchDownloadableFiles(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $limit = $request->limit ?? 15;
        $query = $this->downloadService->getDownloadableFilesQuery($user);

        // Load morph relations: file.fileable (product/variation with shop)
        $query->with(['file.fileable' => function($q) {
            $q->with('shop'); // untuk Product atau Variation->product->shop
        }]);

        $files = $query->paginate($limit);

        return DownloadableFileResource::collection($files);
    }

    /**
     * POST /generate-downloadable-url
     */
    public function generateDownloadableUrl(Request $request)
    {
        $request->validate([
            'digital_file_id' => 'required|exists:digital_files,id',
        ]);

        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $hasAccess = $this->downloadService->userHasAccessToFile($user, $request->digital_file_id);
        if (!$hasAccess) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $token = $this->downloadService->generateDownloadToken($request->digital_file_id, $user->id);

        return response()->json([
            'url' => route('download_url.token', ['token' => $token->token])
        ]);
    }

    /**
     * GET /download/{token} (route name: download_url.token)
     */
    public function downloadFile($token)
    {
        $digitalFile = $this->downloadService->getFileByToken($token);
        if (!$digitalFile) {
            throw new HttpException(404, config('notice.TOKEN_NOT_FOUND'));
        }

        $mediaItem = $this->downloadService->getMediaItem($digitalFile->attachment_id);
        if (!$mediaItem) {
            throw new HttpException(404, config('notice.NOT_FOUND'));
        }

        // Return file download response (Spatie MediaLibrary)
        return $mediaItem;
    }
}