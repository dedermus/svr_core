<?php

namespace Svr\Core\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Svr\Core\Exceptions\CustomException;

class ApiValidationErrors
{
    /**
     * Обработка ошибок.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $response = $next($request);

        if ($response->exception instanceof ValidationException) {
            return $this->handleValidationException($response->exception);
        }

        if ($response->exception instanceof CustomException) {
            return $this->handleCustomException($response->exception);
        }

        return $response;
    }

    /**
     * Обработка исключения валидации.
     *
     * @param ValidationException $exception
     *
     * @return JsonResponse
     */
    private function handleValidationException(ValidationException $exception): JsonResponse
    {
        return $this->makeErrorResponse(
            'Ошибка валидации',
            $exception->errors(),
            422
        );
    }

    /**
     * Обработка кастомного исключения.
     *
     * @param CustomException $exception
     *
     * @return JsonResponse
     */
    private function handleCustomException(CustomException $exception): JsonResponse
    {
        $code = $exception->getCode() ?: 500;
        $response = [
            'status'        => false,
            'message'       => $exception->getMessage(),
            'data'          => [],
            'dictionary'    => [],
            'notifications' => [
                'count_new'   => 0,
                'count_total' => 0
            ],
            'pagination'    => [
                'total_records' => 1,
                'max_page'      => 1,
                'cur_page'      => 1,
                'per_page'      => 1,
            ],
        ];
        if (config('app.debug')) {
            $response['trace'] = array_slice($exception->getTrace(), 0, 1);
        }
        // Ключ `'trace'` добавляется в массив ответа только в том случае, если `config('app.debug')` возвращает `true`.
        // Это позволяет избежать включения трассировки стека в ответ, когда приложение находится в режиме продакшн.
        return response()->json($response, $code);
    }

    /**
     * Создать ответ с ошибкой.
     *
     * @param string $message
     * @param array  $errors
     * @param int    $status
     *
     * @return JsonResponse
     */
    private function makeErrorResponse(string $message, array $errors = [], int $status = 500): JsonResponse
    {
        return response()->json([
            'status'        => false,
            'message'       => $message,
            'errors'        => $errors,
            'data'          => [],
            'dictionary'    => [],
            'notifications' => [
                'count_new'   => 0,
                'count_total' => 0
            ],
            'pagination'    => [
                'total_records' => 1,
                'max_page'      => 1,
                'cur_page'      => 1,
                'per_page'      => 1,
            ],
        ], $status);
    }
}
