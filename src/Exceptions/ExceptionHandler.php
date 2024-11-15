<?php

namespace Svr\Core\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Container\Container;
use Illuminate\Foundation\Configuration\Exceptions;
use OpenAdminCore\Admin\Admin;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler
{
    public function __invoke(Exceptions $exceptions)
    {
        $exceptions->render(function (Throwable $e, Request $request) {
            /** @var ResponseFactory $response */
            $response = Container::getInstance()->make(ResponseFactory::class);

            /**
             * определение кастомного Exceptions для api
             */
            if ($request->is(config('svr.api_prefix') . '/*')) {
                if ($e instanceof NotFoundHttpException) {
                    return $response->make(
                        content: [
                            'status'  => 'error',
                            'message' => $e->getMessage()
                        ],
                        status: $e->getStatusCode(),
                    );
                }
                if ($e instanceof MethodNotAllowedHttpException) {
                    return $response->make(
                        content: [
                            'status'  => 'error',
                            'message' => $e->getMessage()
                        ],
                        status: $e->getStatusCode(),
                    );
                }
                if ($e instanceof InvalidArgumentException) {
                    return $response->make(
                        content: [
                            'status'  => 'error',
                            'message' => $e->getMessage()
                        ],
                        status: $e->getStatusCode(),
                    );
                }
                return false;
            }
        });
    }
}
