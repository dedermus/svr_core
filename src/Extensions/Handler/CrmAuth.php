<?php

namespace Svr\Core\Extensions\Handler;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Svr\Core\Extensions\System\SystemFilter;

class CrmAuth
{
    /**
     * Получение токена авторизации.
     *
     * @return bool
     * @throws ConnectionException
     */
    public static function getToken():bool
    {
        // Получаем параметры для авторизации
        $email = env('CRM_USERNAME');
        $password = env('CRM_PASSWORD');

        // Формируем URL и выполняем запрос
        $response = Http::withUrlParameters([
            'host' => env('CRM_HOST') . '.' . SystemFilter::server_tail(),
            'api' => env('CRM_API'),
            'endpoint' => env('CRM_END_POINT_TOKEN'),
        ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
            'email' => $email,
            'password' => $password,
        ]);

        // Обрабатываем успешный ответ
        if ($response->successful()) {
            $responseData = $response->json();

            if (isset($responseData['data']['token'])) {
                Log::channel('crm')->info('Токен успешно получен.');
                Context::add('crm_token', $responseData['data']['token']);
                return true;
            }

            // Логируем отсутствие токена в ответе
            Log::channel('crm')->warning('Токен не найден в ответе.', $responseData);
        } else {
            // Логируем ошибку сервера
            Log::channel('crm')->error('Ошибка сервера при получении токена.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return false;
    }
}
