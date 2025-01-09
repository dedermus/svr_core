<?php

namespace Svr\Core\Extensions\Handler;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\System\SystemFilter;

class CrmAuth
{
    /**
     * Получение токена авторизации.
     *
     * @return bool
     */
    public static function getToken(): bool
    {
        // Получаем параметры для авторизации
        $email = env('CRM_USERNAME', '');
        $password = env('CRM_PASSWORD', '');
        $host = env('CRM_HOST', '');
        $api = 'allApi';
        $endpoint = 'getToken';

        // Проверка наличия необходимых параметров
        if (empty($email) || empty($password) || empty($host)) {
            Log::channel('crm')->error('Не все параметры окружения установлены для получения токена.');
            return false;
        }

        try {
            // Формируем URL и выполняем запрос
            $response = Http::withUrlParameters([
                'host'     => $host . '.' . SystemFilter::server_tail(),
                'api'     => $api,
                'endpoint' => $endpoint,
            ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
                'email'    => $email,
                'password' => $password,
            ]);

            // Обрабатываем успешный ответ
            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['data']['token'])) {
                    Log::channel('crm')->info('Токен успешно получен.');
                    Context::addHidden('crm_token', $responseData['data']['token']);
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
        } catch (RequestException $e) {
            // Логируем исключение при запросе
            Log::channel('crm')->error('Ошибка при выполнении запроса.', [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ]);
        } catch (Exception $e) {
            // Логируем общее исключение
            Log::channel('crm')->error('Общая ошибка при получении токена.', [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }
}
