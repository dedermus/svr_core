<?php

namespace Svr\Core\Extensions\Handler;

use DateTime;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Svr\Core\Extensions\System\SystemFilter;
use Svr\Directories\Models\DirectoryCountries;

class CrmListCountries
{
    /**
     * Запрос на внешний ресурс для получения справочника стран
     *
     * @return bool
     */
    public static function getListCountries(): bool
    {
        // Проверяем наличие токена
        if (!CrmAuth::getToken()) {
            Log::channel('crm')->warning('Получение справочника стран невозможно: отсутствует токен.');
            return false;
        }

        $host = env('CRM_HOST', '');
        $api = 'allApi';
        $endpoint = 'getListCountries';

        // Проверка наличия необходимых параметров
        if (empty($host)) {
            Log::channel('crm')->error('Не все параметры окружения установлены для получения справочника стран.');
            return false;
        }

        try {
            // Формируем URL и выполняем запрос
            $response = Http::withUrlParameters([
                'host' => $host . '.' . SystemFilter::server_tail(),
                'api' => $api,
                'endpoint' => $endpoint,
            ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
                'token' => Context::getHidden('crm_token'),
            ]);

            // Обрабатываем успешный ответ
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['data'])) {
                    Log::channel('crm')->info('Справочник стран успешно получен.');
                    self::setListCountries($responseData);
                    return true;
                }
                // Логируем отсутствие списка хозяйств в ответе
                Log::channel('crm')->warning('Справочник стран не найден в ответе.', $responseData);
            } else {
                // Логируем ошибку сервера
                Log::channel('crm')->error('Ошибка сервера при получении справочника стран.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
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
            Log::channel('crm')->error('Общая ошибка при получении справочника стран.', [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Обработка данных для создания или обновления справочника стран.
     *
     * @param array $request
     *
     * @return void
     */
    private static function setListCountries(array $request): void
    {
        Log::channel('crm')->info('- Начинаем анализ записей на предмет создания или обновления справочника стран.');

        $date_start = new DateTime();
        $countInset = 0;
        $countUpdate = 0;
        $countError = 0;

        foreach ($request['data'] as $item) {
            $country_ngos = $item['ngos'] ?? false;
            $country_gos = $item['gos'] ?? false;
            $country_name = $item['name'] ?? false;
            $country_kod = $item['oksm'] ?? false;

            $item_exist = DirectoryCountries::where('country_ngos', $country_ngos)->first();

            if (is_null($item_exist)) {
                try {
                    DB::beginTransaction(); // Начинаем транзакцию
                    $countries_item = [
                        'country_guid_self' => Str::uuid()->toString(),
                        'country_ngos'      => $country_ngos,
                        'country_gos'       => $country_gos,
                        'country_name'      => $country_name,
                        'country_kod'       => $country_kod,
                    ];
                    DirectoryCountries::create($countries_item);
                    DB::commit(); // Фиксируем транзакцию
                    $countInset++;
                } catch (Exception $e) {
                    DB::rollBack(); // Откатываем транзакцию в случае ошибки
                    Log::channel('crm')->error(
                        'Ошибка при попытке создания новой страны: ', ['message' => $e->getMessage()]
                    );
                    $countError++;
                }
            } else {
                try {
                    DB::beginTransaction(); // Начинаем транзакцию
                    $item_exist->fill([
                        'country_ngos' => $country_ngos,
                        'country_gos'  => $country_gos,
                        'country_name' => $country_name,
                        'country_kod'  => $country_kod,
                    ])->save();
                    DB::commit(); // Фиксируем транзакцию
                    $countUpdate++;
                } catch (Exception $e) {
                    DB::rollBack(); // Откатываем транзакцию в случае ошибки
                    Log::channel('crm')->error('Ошибка при попытке обновления страны: ', ['message' => $e->getMessage()]
                    );
                    $countError++;
                }
            }
        }

        $date_end = new DateTime();
        $date_diff = $date_start->diff($date_end)->format("%H:%I:%S:%F");

        Log::channel('crm')->info('- Создано стран: ' . $countInset . '.');
        Log::channel('crm')->info('- Обновлено стран: ' . $countUpdate . '.');
        Log::channel('crm')->info('- Ошибки по странам: ' . $countError . '.');
        Log::channel('crm')->info('- Время наполнения базы данных: ' . $date_diff);
    }
}
