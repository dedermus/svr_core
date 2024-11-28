<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemSetting;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthHerriotRequisitesDataResource;
use Svr\Core\Resources\AuthInfoSystemUsersDataResource;
use Svr\Core\Resources\AuthInfoSystemUsersDictionaryResource;
use Svr\Core\Resources\AuthSetSystemUsersDataResource;
use Svr\Core\Resources\SvrApiAuthInfoResource;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Core\Traits\GetDictionary;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class ApiUsersController extends Controller
{
    /**
     * Получение информации о пользователе.
     *
     * @param Request $request
     *
     * @return SvrApiResponseResource|JsonResponse
     */
    public function authInfo(Request $request): SvrApiResponseResource|JsonResponse
    {
        /** @var  $user - получим авторизированного пользователя */
        $user = auth()->user();
        // получим токен текущего пользователя
        $token = $request->bearerToken();
        // получим данные о token из базы
        $token_data = SystemUsersToken::where('token_value', '=', $token)->first()->toArray();
        // запомнили participation_id
        $participation_id = $token_data['participation_id'];
        // получили привязки пользователя
        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);
        // собрали данные для передачи в ресурс
        $data = collect([
            'user' => $user,
            'user_id' => $user['user_id'],
            'user_participation_info' => $user_participation_info,
            'company_data' => DataCompaniesLocations::find($user_participation_info['company_location_id'])->company??"",
            'region_data' => DirectoryCountriesRegion::find($user_participation_info['region_id']),
            'district_data' => DirectoryCountriesRegionsDistrict::find($user_participation_info['district_id']),
            'role_data' => SystemRoles::find($user_participation_info['role_id']),
            'participation_id' => $participation_id,
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAuthInfoResource::class,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Авторизация пользователя
     *
     * @param Request $request
     *
     * @return JsonResponse|SvrApiResponseResource
     */
    public function authLogin(Request $request): SvrApiResponseResource|JsonResponse
    {
        $model = new SystemUsers();
        $user = null; // переменная для пользователя
        $filterKeys = ['user_email', 'user_password'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);

        $credentials = $request->only($filterKeys);

        // Проверить существование пользователя, который активный и не удален
        /** @var SystemUsers $user */
        $users = SystemUsers::where([
            ['user_email', '=', $credentials['user_email']],
            ['user_status', '=', SystemStatusEnum::ENABLED->value],
            ['user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value],
        ])->get();

        // Если получен список пользователей с одним email
        if (!is_null($users)) {
            // переберем пользователей
            foreach ($users as $item) {
                // если email и password совпали
                if ($item && Hash::check($credentials['user_password'], $item->user_password)) {
                    $user = $item;
                    break;  // выйдем из перебора
                }
            }
        }
        // Если пользователь не найден
        if (is_null($user)) { //TODO переписать на нормальный структурированный вид после того как сделаем нормальный конструктор вывода
            return response()->json(['error' => 'Неправильный логин или пароль'], 401);
        }
        // Выдать токен пользователю
        $token = $user->createToken('auth_token')->plainTextToken;

        // Последний токен пользователя
        $last_token = SystemUsersToken::userLastTokenData($user->user_id);

        if ($last_token) {
            $last_token = $last_token->toArray();
            $participation_id = $last_token['participation_id'] ?? null;
        } else {
            // получаем связку пользователя с хозяйствами/регионами/районами
            $participation_last = DataUsersParticipations::where([
                ['user_id', '=', $user['user_id']],
                ['participation_status', '=', SystemStatusEnum::ENABLED->value]
                ])
                ->latest('updated_at')
                ->first();
            // если привязка есть
            if (!is_null($participation_last)) {
                $participation_id = $participation_last['participation_id'];
            } else {
                return response()->json(['error' => 'Пользователь не привязан ни к одному хозяйству/району/региону'], 401);
            }
        }

        // Создали запись в таблице токенов
        (new SystemUsersToken())->userTokenStore([
            'user_id' => $user['user_id'],
            'participation_id' => $participation_id,
            'token_value' => $token,
            'token_client_ip' => $request->ip()
        ]);

        // Подготовили данные для передачи в ресурс
        $data = collect([
            'user_token' => $token,
            'user' => $user,
            'user_participation_info' => DataUsersParticipations::userParticipationInfo($participation_id),
            'user_roles_list' => SystemUsersRoles::userRolesList($user['user_id'])->all(),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($user['user_id'])->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($user['user_id'])->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($user['user_id'])->all(),
            'avatars' => (new SystemUsers())->getCurrentUserAvatar($user['user_id']),
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => '',
            'response_resource_data' => AuthInfoSystemUsersDataResource::class,
            'response_resource_dictionary' => AuthInfoSystemUsersDictionaryResource::class,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Выход пользователя
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function authLogout(Request $request): JsonResponse
    {
        // получим пользователя
        $user = auth()->user();

        // обновим статус токена
        SystemUsersToken::where([
            ['user_id', '=', $user['user_id']],
            ['token_value', '=', $user['token']]
        ])->update(['token_status' => SystemStatusEnum::DISABLED->value]);

        // обновим статус токена Sanctum
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'user logged out'], 200);
    }

    /**
     * Установить (выбрать) привязку к компании, региону, району
     *
     * @param Request $request
     *
     * @return SvrApiResponseResource
     */
    public function authSet(Request $request): SvrApiResponseResource
    {
        // валидация запроса
        $model = new DataUsersParticipations();
        // - готовим список необходимых полей для валидации
        $filterKeys = ['participation_item_id', 'participation_item_type'];
        // - собираем набор правил и сообщений
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        // - переопределяем ключи и правила
        $rules['participation_type'] = $rules['participation_item_type'];
        $rules['participation_item_id'] = "required|".$rules['participation_item_id'];
        $messages['participation_type'] = $messages['participation_item_type'];
        // ... TODO - подумать об оптимизации данного костыля
        // - удаляем ненужные ключи
        unset($rules['participation_item_type']);
        unset($messages['participation_item_type']);
        // - проверяем запрос на валидацию
        $request->validate($rules, $messages);

        // логика
        // - получим пользователя
        $user = auth()->user();
        // - проверим возможность привязки пользователя
        $participation = DB::table((new DataUsersParticipations())->getTable().' as dup')
            ->select('dup.*')
            ->leftjoin(SystemUsers::getTableName().' AS su', 'su.user_id', '=', 'dup.user_id')
            ->leftjoin(SystemUsersToken::getTableName().' AS sut', 'sut.user_id', '=', 'sut.user_id')
            ->where([
                ['su.user_id', '=', $user['user_id']],
                ['sut.token_value', '=', $user['token']],
                ['dup.participation_item_id', '=', $request->query('participation_item_id')],
                ['dup.participation_item_type', '=', $request->query('participation_type')]
            ])
            ->first();

        // - если такая привязка может быть
        if (!is_null($participation))
        {
            // - переопределение имени поля
            $request->merge([
                'participation_item_type' => $request->query('participation_type')
            ]);
            // - делаем привязку
            SystemUsersToken::where([
                ['token_value', '=', $user['token']]
            ])->update([
                'participation_id' => $participation->participation_id,
            ]);
        }

        $response_messages = is_null($participation)
            ? 'Нет доступа к запрошенной привязке'
            : 'Привязка успешно установлена';

        // Подготовили данные для передачи в ресурс
        $data = collect([
            'user_token' => $user['token'],
            'user' => $user,
            'user_participation_info' => DataUsersParticipations::userParticipationInfo($user['participation_id']),
            'user_roles_list' => SystemUsersRoles::userRolesList($user['user_id'])->all(),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($user['user_id'])->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($user['user_id'])->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($user['user_id'])->all(),
            'avatars' => (new SystemUsers())->getCurrentUserAvatar($user['user_id']),
            'user_id' => $user['user_id'],
            'status' => !is_null($participation),
            'user_companies_count' => DataUsersParticipations::getUsersCompaniesCount($user['user_id']),
            'message' => $response_messages,
            'response_resource_data' => AuthSetSystemUsersDataResource::class,
            'response_resource_dictionary' => AuthInfoSystemUsersDictionaryResource::class,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование реквизитов для подключения к хорриоту
     * @param Request $request
     *
     * @return SvrApiResponseResource
     */
    public function authHerriotRequisites(Request $request): SvrApiResponseResource
    {
        // валидация запроса
        $model = new SystemUsers();
        // - готовим список необходимых полей для валидации
        // Поля из запроса              Поля из базы
        // herriot_api_key          - user_herriot_apikey
        // herriot_issuer_id        - user_herriot_issuerid
        // herriot_login            - user_herriot_login
        // herriot_password         - user_herriot_password

        $filterKeys = ['user_herriot_apikey', 'user_herriot_issuerid', 'user_herriot_login', 'user_herriot_password'];
        // - собираем набор правил и сообщений
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        // - переопределяем ключи и правила
        $rules['herriot_api_key'] = "required|".$rules['user_herriot_apikey'];
        $rules['herriot_issuer_id'] = "required|".$rules['user_herriot_issuerid'];
        $rules['herriot_login'] = "required|".$rules['user_herriot_login'];
        $rules['herriot_password'] = "required|".$rules['user_herriot_password'];
        $messages['herriot_api_key'] = $messages['user_herriot_apikey'];
        $messages['herriot_issuer_id'] = $messages['user_herriot_issuerid'];
        $messages['herriot_login'] = $messages['user_herriot_login'];
        $messages['herriot_password'] = $messages['user_herriot_password'];
        // ... TODO - подумать об оптимизации данного костыля
        // - удаляем ненужные ключи
        unset($rules['user_herriot_apikey']);
        unset($rules['user_herriot_issuerid']);
        unset($rules['user_herriot_login']);
        unset($rules['user_herriot_password']);
        unset($messages['user_herriot_apikey']);
        unset($messages['user_herriot_issuerid']);
        unset($messages['user_herriot_login']);
        unset($messages['user_herriot_password']);
        // - проверяем запрос на валидацию
        $request->validate($rules, $messages);

        // логика
        // - обновляем значения
        // - APIKey хорриота
        SystemSetting::where([
            ['setting_code', '=', 'herriot_api_key']
        ])->update([
            'setting_value' => $request->query('herriot_api_key'),
        ]);
        // - IssuerId Хорриота
        SystemSetting::where([
            ['setting_code', '=', 'herriot_issuer_id']
        ])->update([
            'setting_value' => $request->query('herriot_issuer_id'),
        ]);
        // - Логин в API herriot
        SystemSetting::where([
            ['setting_code', '=', 'herriot_login']
        ])->update([
            'setting_value' => $request->query('herriot_login'),
        ]);
        // - Пароль в API herriot
        SystemSetting::where([
            ['setting_code', '=', 'herriot_password']
        ])->update([
            'setting_value' => $request->query('herriot_password'),
        ]);

        // - получим пользователя
        $user = auth()->user();

        // Подготовили данные для передачи в ресурс
        $data = collect([
            'user_id' => $user['user_id'],
            'status' => true,
            'message' => 'Реквизиты установлены',
            'response_resource_data' => AuthHerriotRequisitesDataResource::class,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }
}
