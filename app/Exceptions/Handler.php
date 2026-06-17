<?php

namespace App\Exceptions;

use Exception;

class Handler extends Exception
{
    public function render($request, Throwable $exception)
{
    if ($request->expectsJson()) {
        $code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
        $message = $exception->getMessage() ?: 'Server Error';
        $errors = null;
        if ($exception instanceof ValidationException) {
            $code = 422;
            $message = 'Validation failed';
            $errors = $exception->errors();
        }
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
    return parent::render($request, $exception);
}
}
