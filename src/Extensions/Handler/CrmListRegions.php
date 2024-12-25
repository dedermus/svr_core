<?php

namespace Svr\Core\Extensions\Handler;

use DateTime;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Svr\Core\Extensions\System\SystemFilter;
use Svr\Directories\Models\DirectoryCountries;
use Svr\Directories\Models\DirectoryCountriesRegion;

class CrmListRegions
{
    /**
     * Запрос на внешний ресурс для получения регионов стран
     *
     * @return bool
     */
    public static function getListRegions(): bool
    {
        // Проверяем наличие токена
        if (!CrmAuth::getToken()) {
            Log::channel('crm')->warning('Получение справочника регионов стран невозможно: отсутствует токен.');
            return false;
        }

        $host = env('CRM_HOST', '');
        $api = 'allApi';
        $endpoint = 'getListRegions';

        // Проверка наличия необходимых параметров
        if (empty($host)) {
            Log::channel('crm')->error('Не все параметры окружения установлены для получения справочника регионов стран.');
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
                    Log::channel('crm')->info('Справочник регионов стран успешно получен.');
                    self::setListRegions($responseData);
                    return true;
                }
                // Логируем отсутствие списка хозяйств в ответе
                Log::channel('crm')->warning('Справочник регионов стран не найден в ответе.', $responseData);
            } else {
                // Логируем ошибку сервера
                Log::channel('crm')->error('Ошибка сервера при получении справочника регионов стран.', [
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
            Log::channel('crm')->error('Общая ошибка при получении справочника регионов стран.', [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Обработка данных для создания или обновления справочника регионов стран.
     *
     * @param array $request
     *
     * @return void
     */
    private static function setListRegions(array $request): void
    {
        Log::channel('crm')->info('- Начинаем анализ записей на предмет создания или обновления справочника регионов стран.');

        $date_start = new DateTime();
        $countInset = 0;
        $countUpdate = 0;
        $countError = 0;

        foreach ($request['data'] as $item) {
            $code_gos = $item['code_gos'] ?? false;
            $region_id = $item['code'] ?? false;
            $region_obl = $item['code_obl'] ?? false;
            $region_name = $item['name'] ?? false;

            $countries_exist = DirectoryCountries::where('country_ngos', $code_gos)->first();

            if (!is_null($countries_exist)) {
                $country_id = $countries_exist->country_id;
                $regions_exist = DirectoryCountriesRegion::where('region_id', $region_id)->first();
                if (is_null($regions_exist)) {
                    try {
                        DB::beginTransaction(); // Начинаем транзакцию
                        $regions_item = [
                            'region_id'   => $region_id,
                            'region_obl'  => $region_obl,
                            'country_id'  => $country_id,
                            'region_name' => $region_name,
                        ];
                        DirectoryCountriesRegion::create($regions_item);
                        DB::commit(); // Фиксируем транзакцию
                        $countInset++;
                    } catch (Exception $e) {
                        DB::rollBack(); // Откатываем транзакцию в случае ошибки
                        Log::channel('crm')->error(
                            'Ошибка при попытке создания нового региона страны: ', ['message' => $e->getMessage()]
                        );
                        $countError++;
                    }
                } else {
                    try {
                        DB::beginTransaction(); // Начинаем транзакцию
                        $regions_exist->fill([
                            'country_id' => $country_id,
                            'region_name'  => $region_name,
                        ])->save();
                        DB::commit(); // Фиксируем транзакцию
                        $countUpdate++;
                    } catch (Exception $e) {
                        DB::rollBack(); // Откатываем транзакцию в случае ошибки
                        Log::channel('crm')->error('Ошибка при попытке обновления региона страны: ', ['message' => $e->getMessage()]
                        );
                        $countError++;
                    }
                }
            }
        }

        $date_end = new DateTime();
        $date_diff = $date_start->diff($date_end)->format("%H:%I:%S:%F");

        Log::channel('crm')->info('- Создано регионов стран: ' . $countInset . '.');
        Log::channel('crm')->info('- Обновлено регионов стран: ' . $countUpdate . '.');
        Log::channel('crm')->info('- Ошибки по регионам стран: ' . $countError . '.');
        Log::channel('crm')->info('- Время наполнения базы данных: ' . $date_diff);
    }
}
