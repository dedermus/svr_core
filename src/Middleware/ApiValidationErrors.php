<?php

namespace Svr\Core\Middleware;

use Closure;
use Exception;
use http\Exception\InvalidArgumentException;
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
            $code = 200;
            if ($errors->getCode() != 0) {
                $code = $errors->getCode();
            } else {
                $code = $response->getStatusCode();
            }
            return response()->json([
                'status'  => 'error',
                'message' => $errors->getMessage(),
                'data'  => [],
            ], $code);
        }
        return $response;
    }
}
