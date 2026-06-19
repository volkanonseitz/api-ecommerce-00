<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if (! $request->expectsJson()) {
            return parent::render($request, $exception);
        }

        $status = 500;
        $message = 'Internal Server Error';
        $errors = null;

        if ($exception instanceof ValidationException) {
            $status = 422;
            $message = 'Validasi gagal.';
            $errors = $exception->errors();
        } elseif ($exception instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated.';
        } elseif ($exception instanceof AuthorizationException) {
            $status = 403;
            $message = 'Forbidden.';
        } elseif ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Data tidak ditemukan.';
        }

        return response()->json([
            'success' => false,
            'code' => $status,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
