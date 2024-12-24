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
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;

class CrmListFarms
{
    /**
     * Запрос на внешний ресурс для получения списка хозяйств
     * @return bool
     */
    public static function getListFarms(): bool
    {
        // Проверяем наличие токена
        if (!CrmAuth::getToken()) {
            Log::channel('crm')->warning('Получение списка хозяйств невозможно: отсутствует токен.');
            return false;
        }

        try {
            // Формируем URL и выполняем запрос
            $response = Http::withUrlParameters([
                'host' => env('CRM_HOST') . '.' . SystemFilter::server_tail(),
                'api' => env('CRM_API'),
                'endpoint' => env('CRM_END_POINT_FARMS'),
            ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
                'token' => Context::getHidden('crm_token'),
            ]);

            //Context::forget('crm_token');

            // Обрабатываем успешный ответ
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['data'])) {
                    Log::channel('crm')->info('Список хозяйств успешно получен.');
                    self::setListFarms($responseData);
                    return true;
                }

                // Логируем отсутствие списка хозяйств в ответе
                Log::channel('crm')->warning('Список хозяйств не найден в ответе.', $responseData);
            } else {
                // Логируем ошибку сервера
                Log::channel('crm')->error('Ошибка сервера при получении списка хозяйств.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (RequestException $e) {
            // Логируем исключение при запросе
            Log::channel('crm')->error('Ошибка при выполнении запроса.', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        } catch (Exception $e) {
            // Логируем общее исключение
            Log::channel('crm')->error('Общая ошибка при получении списка хозяйств.', [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Обработка данных для создания или обновления компаний.
     *
     * @param array $request
     *
     * @return void
     */
    private static function setListFarms(array $request): void
    {
        Log::channel('crm')->info('- Начинаем анализ записей на предмет создания или обновления компаний.');

        $date_start = new DateTime();
        $count_inset = 0;
        $count_update = 0;

        try {
            foreach ($request['data'] as $item) {
                DB::beginTransaction();
                $company_base_index = $item['base_index'] ?? false;
                $company_inn = $item['company_inn'] ?? false;

                if ($company_base_index && $company_inn) {
                    $res = DataCompanies::where('company_base_index', $company_base_index)->first();

                    if (is_null($res)) {
                        // Создание новой компании
                        $company_item = [
                            'company_base_index' => $company_base_index,
                            'company_guid'     => Str::uuid()->toString(),
                            'company_name_short' => $item['company_name_short'],
                            'company_name_full' => $item['company_name_full'],
                            'company_address'    => $item['jur_address'],
                            'company_inn'        => $item['company_inn'],
                            'company_kpp'        => $item['company_kpp'],
                        ];
                        $company = DataCompanies::create($company_item);

                        // Создание локации компании
                        $location_item = [
                            'company_id' => $company->company_id,
                            'region_id' => $item['nobl'],
                            'district_id'=> $item['nrn'],
                        ];
                        DataCompaniesLocations::created($location_item);

                        $count_inset++;
                    } else {
                        // Обновление существующей компании
                        $res->fill([
                            'company_name_short' => $item['company_name_short'],
                            'company_name_full' => $item['company_name_full'],
                            'company_address'    => $item['jur_address'],
                            'company_inn'        => $item['company_inn'],
                            'company_kpp'        => $item['company_kpp'],
                        ])->save();
                        // TODO - У нас может быть несколько локаций компании, надо об этом помнить,
                        // так как обновление произойдет для всех локаций компании по её company_id
                        // Обновление локации компании
                        DataCompaniesLocations::where('company_id', $res->company_id)->update([
                            'region_id' => $item['nobl'],
                            'district_id' => $item['nrn'],
                        ]);

                        $count_update++;
                    }
                } else {
                    Log::channel('crm')->warning('У хозяйства нет ИНН или Базового индекса.', $item);
                }
                DB::commit();
            }

        } catch (\Exception $e) {
            Log::channel('crm')->error('Ошибка при обработке данных компаний.', ['message' => $e->getMessage()]);
        }

        $date_end = new DateTime();
        $date_diff = $date_start->diff($date_end)->format("%H:%I:%S:%F");

        Log::channel('crm')->info('- Создано компаний: ' . $count_inset . '.');
        Log::channel('crm')->info('- Обновлено компаний: ' . $count_update . '.');
        Log::channel('crm')->info('- Время наполнения базы данных: ' . $date_diff);
    }
}
