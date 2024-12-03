<?php

namespace Svr\Core\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApiValidationErrors
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $response = $next($request);

        // Обработка исключения валидации (ValidationException)
        if ($response->exception instanceof ValidationException) {
            $errors = $response->exception->errors();

            return response()->json([
                'status'  => 'error',
                'message' => 'Ошибка валидации',
                'errors'  => $errors,
                'data'  => [],
            ], 422);
        }
        // Обработка исключения Exception
        if ($response->exception instanceof Exception) {
            $errors = $response->exception;
            return response()->json([
                'status'  => 'error',
                'message' => $errors->getMessage(),
                'data'  => [],
            ], $errors->getCode());
        }
        return $response;
    }
}
