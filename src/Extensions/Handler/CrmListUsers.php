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
use Svr\Core\Enums\SystemParticipationsTypesEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Extensions\System\SystemFilter;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;

class CrmListUsers
{
    /**
     * Запрос на внешний ресурс для получения списка пользователей
     *
     * @return bool
     */
    public static function getListUsers(): bool
    {
        // Проверяем наличие токена
        if (!CrmAuth::getToken()) {
            Log::channel('crm')->warning('Получение списка пользователей невозможно: отсутствует токен.');
            return false;
        }

        $host = env('CRM_HOST', '');
        $api = 'allApi';
        $endpoint = 'getListUsers';

        // Проверка наличия необходимых параметров
        if (empty($host)) {
            Log::channel('crm')->error('Не все параметры окружения установлены для получения списка пользователей.');
            return false;
        }

        try {
            // Формируем URL и выполняем запрос
            $response = Http::withUrlParameters([
                'host'     => $host . '.' . SystemFilter::server_tail(),
                'api'      => $api,
                'endpoint' => $endpoint,
            ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
                'token' => Context::getHidden('crm_token'),
            ]);

            // Обрабатываем успешный ответ
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['data'])) {
                    Log::channel('crm')->info('Список пользователей успешно получен.');
                    return self::setListUsers($responseData);
                }

                // Логируем отсутствие списка пользователей в ответе
                Log::channel('crm')->warning('Список пользователей не найден в ответе.', $responseData);
            } else {
                // Логируем ошибку сервера
                Log::channel('crm')->error('Ошибка сервера при получении списка пользователей.', [
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
            Log::channel('crm')->error('Общая ошибка при получении списка пользователей.', [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Обработка данных для создания новых пользователей.
     *
     * @param array $request
     *
     * @return bool
     */
    private static function setListUsers(array $request): bool
    {
        Log::channel('crm')->info('- Начинаем анализ записей на предмет создания новых пользователей.');

        $date_start = new DateTime();
        $countInset = 0;
        $countUpdate = 0;

        $roleSlug = 'doctor_company';
        $role = SystemRoles::where('role_slug', $roleSlug)->first();
        $roleId = $role ? $role->role_id : null;

        if (!$roleId) {
            Log::channel('crm')->error(
                'Роль ' . $roleSlug . ' не найдена. Дальнейшая обработка списка новых пользователей невозможна.'
            );
            return false;
        }

        Log::channel('crm')->info('Роль ' . $roleSlug . ' найдена.');

        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {

                $companyBaseIndex = $item['base_index'] ?? null;
                $userEmail = $item['user_email'] ?? null;
                $userPassword = isset($item['user_password']) ? base64_decode($item['user_password']) : null;

                if ($companyBaseIndex && $userEmail && $userPassword) {
                    $user = SystemUsers::where('user_base_index', $companyBaseIndex)->first();

                    if (!$user) {
                        // Создание нового пользователя
                        $user = SystemUsers::create([
                            'user_guid'       => (string)Str::uuid(),
                            'user_first'      => 'Имя',
                            'user_base_index' => $companyBaseIndex,
                            'user_middle'     => 'Отчество',
                            'user_last'       => 'Фамилия',
                            'user_password'   => bcrypt($userPassword),
                            'user_email'      => $userEmail,
                            'user_status'     => SystemStatusEnum::DISABLED->value,
                        ]);

                        $company = DataCompanies::where('company_base_index', $companyBaseIndex)->first();
                        if ($company) {
                            $location = DataCompaniesLocations::where('company_id', $company->company_id)->first();
                            if ($location) {
                                DataUsersParticipations::create([
                                    'user_id'                 => $user->user_id,
                                    'participation_item_type' => SystemParticipationsTypesEnum::COMPANY->value,
                                    'participation_item_id'   => $location->company_location_id,
                                    'role_id'                 => $roleId,
                                ]);
                            }
                        }

                        SystemUsersRoles::create([
                            'user_id'   => $user->user_id,
                            'role_slug' => $roleSlug,
                        ]);

                        $countInset++;
                    } else {
                        $countUpdate++;
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('crm')->error('Ошибка при обработке создания новых пользователей: ' . $e->getMessage());
            return false;
        }
        $date_end = new DateTime();
        $date_diff = $date_start->diff($date_end)->format("%H:%I:%S:%F");

        Log::channel('crm')->info('- Создано новых пользователей: ' . $countInset . '.');
        Log::channel('crm')->info('- Такие пользователи уже есть: ' . $countUpdate . '.');
        Log::channel('crm')->info('- Время наполнения базы данных: ' . $date_diff);
        return true;
    }
}
