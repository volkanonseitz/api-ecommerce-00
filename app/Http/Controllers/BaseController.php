<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseController extends Controller
{
    /**
     * Success response tanpa paginasi
     */
    protected function sendSuccess($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Error response
     */
    protected function sendError(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Response dengan paginasi (otomatis deteksi jika $data adalah LengthAwarePaginator)
     */
    protected function sendPaginated($data, string $message = 'Data retrieved successfully', int $code = 200): JsonResponse
    {
        if ($data instanceof LengthAwarePaginator) {
            return response()->json([
                'success' => true,
                'code'    => $code,
                'message' => $message,
                'data'    => $data->items(),
                'meta'    => [
                    'currentPage' => $data->currentPage(),
                    'totalPages'  => $data->lastPage(),
                    'totalItems'  => $data->total(),
                    'itemsPerPage'=> $data->perPage(),
                ],
            ], $code);
        }

        // Jika tidak paginasi, fallback ke sendSuccess
        return $this->sendSuccess($data, $message, $code);
    }
}