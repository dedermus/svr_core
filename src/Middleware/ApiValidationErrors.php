<?php

namespace Svr\Core\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use TypeError;

class ApiValidationErrors
{
    /**
     * Обработка ошибок
     * @param Request $request
     * @param Closure $next
     *
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $response = $next($request);

        // Обработка исключения валидации (ValidationException)
        if ($response->exception instanceof ValidationException) {
            $errors = $response->exception->errors();

            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка валидации',
                'errors' => $errors,
                'data'    => [],
            ], 422);
        }

        // Обработка исключения Exception
        if ($response->exception instanceof Exception) {
            $errors = $response->exception;
            $code = $errors->getCode() ?: $response->getStatusCode();

            return response()->json([
                'status' => 'error',
                'message' => $errors->getMessage(),
                'trace' => config('app.debug') ? $errors->getTrace() : [],
                'data'    => [],
            ], $code);
        }

        // Обработка исключения TypeError
        if ($response->exception instanceof TypeError) {
            $errors = $response->exception;
            $code = $errors->getCode() ?: $response->getStatusCode();

            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка типа',
                'trace' => config('app.debug') ? $errors->getTrace() : [],
                'data'    => [],
            ], $code);
        }

        return $response;
    }
}
