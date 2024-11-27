<?php

namespace Svr\Core\Traits;

use Illuminate\Http\Request;

trait GetValidationRules
{
    /**
     * Получить правила валидации по переданному фильтру полей
     *
     * @param Request $request      - Запрос
     * @param         $filterKeys   - Список необходимых полей
     *
     * @return array
     */
    public static function getFilterValidationRules(Request $request, $filterKeys): array
    {
        return array_intersect_key(with(new static)->getValidationRules($request), array_flip($filterKeys));
    }

    /**
     * Получить сообщения об ошибках валидации по переданному фильтру полей
     * @param $filterKeys   - Список необходимых полей
     *
     * @return array
     */
    public function getFilterValidationMessages($filterKeys): array
    {
        return array_intersect_key(with(new static)->getValidationMessages(), array_flip($filterKeys));
    }

    /**
     * Валидация запроса
     * @param Request $request
     *
     * @return void
     */
    private function validateRequest(Request $request): void
    {
        $rules = with(new static)->getValidationRules($request);
        $messages = with(new static)->getValidationMessages();
        $request->validate($rules, $messages);
    }
}
