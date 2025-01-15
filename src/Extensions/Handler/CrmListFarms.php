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

        $host = env('CRM_HOST', '');
        $api = 'allApi';
        $endpoint = 'getListFarms';

        // Проверка наличия необходимых параметров
        if (empty($host)) {
            Log::channel('crm')->error('Не все параметры окружения установлены для получения списка хозяйств.');
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
                    Log::channel('crm')->info('Список компаний успешно получен.');
                    self::setListFarms($responseData);
                    return true;
                }
                // Логируем отсутствие списка хозяйств в ответе
                Log::channel('crm')->warning('Список компаний не найден в ответе.', $responseData);
            } else {
                // Логируем ошибку сервера
                Log::channel('crm')->error('Ошибка сервера при получении списка компаний.', [
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
            Log::channel('crm')->error('Общая ошибка при получении списка компаний.', [
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
        $countInset = 0;
        $countUpdate = 0;
        $countError = 0;

        foreach ($request['data'] as $item) {
            $company_base_index = $item['base_index'] ?? false;
            $company_inn = $item['company_inn'] ?? false;

            if ($company_base_index && $company_inn) {
                $res = DataCompanies::where('company_base_index', $company_base_index)->first();

                if (is_null($res)) {
                    try {
                        DB::beginTransaction(); // Начинаем транзакцию
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

                        $location_item = [
                            'company_id' => $company->company_id,
                            'region_id' => $item['nobl'],
                            'district_id'=> $item['nrn'],
                        ];
                        DataCompaniesLocations::create($location_item);
                        DB::commit(); // Фиксируем транзакцию
                        $countInset++;
                    } catch (Exception $e) {
                        DB::rollBack(); // Откатываем транзакцию в случае ошибки
                        Log::channel('crm')->error('Ошибка при попытке создания новой компании: ', ['message' => $e->getMessage()]);
                        $countError++;
                    }
                } else {
                    try {
                        DB::beginTransaction(); // Начинаем транзакцию
                        $res->fill([
                            'company_name_short' => $item['company_name_short'],
                            'company_name_full'  => $item['company_name_full'],
                            'company_address'    => $item['jur_address'],
                            'company_inn'        => $item['company_inn'],
                            'company_kpp'        => $item['company_kpp'],
                        ])->save();

                        DataCompaniesLocations::where('company_id', $res->company_id)->update([
                            'region_id'   => $item['nobl'],
                            'district_id' => $item['nrn'],
                        ]);
                        DB::commit(); // Фиксируем транзакцию
                        $countUpdate++;
                    } catch (Exception $e) {
                        DB::rollBack(); // Откатываем транзакцию в случае ошибки
                        Log::channel('crm')->error('Ошибка при попытке обновления компании: ', ['message' => $e->getMessage()]);
                        $countError++;
                    }
                }
            } else {
                Log::channel('crm')->warning('У компании нет ИНН или Базового индекса.', $item);
                $countError++;
            }
        }

        $date_end = new DateTime();
        $date_diff = $date_start->diff($date_end)->format("%H:%I:%S:%F");

        Log::channel('crm')->info('- Создано компаний: ' . $countInset . '.');
        Log::channel('crm')->info('- Обновлено компаний: ' . $countUpdate . '.');
        Log::channel('crm')->info('- Ошибки по компаниям: ' . $countError . '.');
        Log::channel('crm')->info('- Время наполнения базы данных: ' . $date_diff);
    }
}
