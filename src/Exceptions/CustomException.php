<?php

namespace Svr\Core\Exceptions;

use Exception;

class CustomException extends Exception
{
    /**
     * Создание нового экземпляра кастомного исключения.
     *
     * @param string $message Сообщение об ошибке.
     * @param int $code Код ошибки.
     * @param Exception|null $previous Предыдущее исключение.
     */
    public function __construct(string $message = "", int $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
