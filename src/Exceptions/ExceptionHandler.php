<?php

namespace Svr\Core\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Container\Container;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Response;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler
{
    public function __invoke(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request) {
            /** @var ResponseFactory $response */
            $response = Container::getInstance()->make(ResponseFactory::class);

            // Определение кастомного Exceptions для API
            if ($request->is(config('svr.api_prefix') . '/*')) {
                $statusCode = $this->getStatusCode($e);
                if ($statusCode !== null) {
                    return $this->makeErrorResponse($response, $e->getMessage(), $statusCode);
                }

                return false;
            }
        });
    }

    /**
     * Получить код состояния для исключения.
     *
     * @param Throwable $e
     * @return int|null
     */
    private function getStatusCode(Throwable $e): ?int
    {
        return match (true) {
            $e instanceof NotFoundHttpException, $e instanceof MethodNotAllowedHttpException => $e->getStatusCode(),
            $e instanceof AuthenticationException => 401,
            $e instanceof InvalidArgumentException => 400,
            default => null,
        };
    }

    /**
     * Создать ответ с ошибкой.
     *
     * @param ResponseFactory $response
     * @param string $message
     * @param int $status
     * @return Response
     */
    private function makeErrorResponse(ResponseFactory $response, string $message, int $status): Response
    {
        return $response->make(
            content: [
                'status' => false,
                'message' => $message,
                'data' => [],
                'dictionary' => [],
                'notifications' => [
                    'count_new' => 0,
                    'count_total' => 0
                ],
                'pagination' => [
                    'total_records' => 1,
                    'max_page' => 1,
                    'cur_page' => 1,
                    'per_page' => 1,
                ],
            ],
            status: $status,
        );
    }
}
