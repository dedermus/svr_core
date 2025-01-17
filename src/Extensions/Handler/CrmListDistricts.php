<?php

namespace Svr\Core\Extensions\Handler;

use DateTime;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\System\SystemFilter;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class CrmListDistricts
{
    /**
     * Запрос на внешний ресурс для получения справочника районов
     *
     * @return bool
     */
    public static function getListDistricts(): bool
    {
        // Проверяем наличие токена
        if (!CrmAuth::getToken()) {
            Log::channel('crm')->warning('Получение справочника районов невозможно: отсутствует токен.');
            return false;
        }

        $host = env('CRM_HOST', '');
        $api = 'allApi';
        $endpoint = 'getListDistricts';

        // Проверка наличия необходимых параметров
        if (empty($host)) {
            Log::channel('crm')->error('Не все параметры окружения установлены для получения справочника районов.');
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
                    Log::channel('crm')->info('Справочник районов успешно получен.');
                    self::setListDistricts($responseData);
                    return true;
                }
                // Логируем отсутствие списка хозяйств в ответе
                Log::channel('crm')->warning('Справочник районов не найден в ответе.', $responseData);
            } else {
                // Логируем ошибку сервера
                Log::channel('crm')->error('Ошибка сервера при получении справочника районов.', [
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
            Log::channel('crm')->error('Общая ошибка при получении справочника районов.', [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Обработка данных для создания или обновления справочника районов.
     *
     * @param array $request
     *
     * @return void
     */
    private static function setListDistricts(array $request): void
    {
        Log::channel('crm')->info('- Начинаем анализ записей на предмет создания или обновления справочника районов.');

        $date_start = new DateTime();
        $countInset = 0;
        $countUpdate = 0;
        $countError = 0;

        foreach ($request['data'] as $item) {
            $code_obl = $item['code_obl'] ?? false;
            $country_ngos = $item['code_gos'] ?? false;
            $district_id = $item['code'] ?? false;
            $district_rn = $item['code_rn'] ?? false;
            $district_name = $item['name'] ?? false;

            $regions_exist = DirectoryCountriesRegion::where('region_id', $code_obl)->first();

            if (!is_null($regions_exist)) {
                $region_id = $regions_exist->region_id;
                $districts_exist = DirectoryCountriesRegionsDistrict::where('district_id', $district_id)->first();
                if (is_null($districts_exist)) {
                    try {
                        DB::beginTransaction(); // Начинаем транзакцию
                        $districts_item = [
                            'district_id'   => $district_id,
                            'district_rn'  => $district_rn,
                            'region_id'  => $region_id,
                            'country_ngos' => $country_ngos,
                            'district_name' => $district_name
                        ];
                        DirectoryCountriesRegionsDistrict::create($districts_item);
                        DB::commit(); // Фиксируем транзакцию
                        $countInset++;
                    } catch (Exception $e) {
                        DB::rollBack(); // Откатываем транзакцию в случае ошибки
                        Log::channel('crm')->error(
                            'Ошибка при попытке создания нового района: ', ['message' => $e->getMessage()]
                        );
                        $countError++;
                    }
                } else {
                    try {
                        DB::beginTransaction(); // Начинаем транзакцию
                        $regions_exist->fill([
                            'district_rn' => $district_rn,
                            'region_id'  => $region_id,
                            'country_ngos' => $country_ngos,
                            'district_name' => $district_name
                        ])->save();
                        DB::commit(); // Фиксируем транзакцию
                        $countUpdate++;
                    } catch (Exception $e) {
                        DB::rollBack(); // Откатываем транзакцию в случае ошибки
                        Log::channel('crm')->error('Ошибка при попытке обновления района: ', ['message' => $e->getMessage()]
                        );
                        $countError++;
                    }
                }
            }
        }

        $date_end = new DateTime();
        $date_diff = $date_start->diff($date_end)->format("%H:%I:%S:%F");

        Log::channel('crm')->info('- Создано районов: ' . $countInset . '.');
        Log::channel('crm')->info('- Обновлено районов: ' . $countUpdate . '.');
        Log::channel('crm')->info('- Ошибки по районам: ' . $countError . '.');
        Log::channel('crm')->info('- Время наполнения базы данных: ' . $date_diff);
    }
}
