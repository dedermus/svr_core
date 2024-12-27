<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Exceptions\CustomException;
use Svr\Core\Jobs\ProcessHerriotUpdateCompanies;
use Svr\Core\Jobs\ProcessHerriotUpdateCompaniesObjects;
use Svr\Core\Jobs\ProcessHerriotUpdateDirectories;
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
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class ApiAuthController extends Controller
{
    /**
     * Получение информации о пользователе.
     *
     * @param Request $request HTTP запрос с токеном авторизации.
     *
     * @return SvrApiResponseResource|JsonResponse Возвращает ресурс с данными пользователя или JSON ответ с ошибкой.
     * @throws CustomException
     */
    public function authInfo(Request $request): SvrApiResponseResource|JsonResponse
    {
        //ProcessHerriotUpdateCompanies::dispatch(4)->onQueue(env('QUEUE_HERRIOT_COMPANIES', 'herriot_companies'));
        ProcessHerriotUpdateCompaniesObjects::dispatch(4)->onQueue(env('QUEUE_HERRIOT_COMPANIES_OBJECTS', 'herriot_companies_objects'));
        /*$hh = new ApiHerriot('vukemkuz-240202', 'bQ34tHHq4');

        $hh->getCompanyObjectsByGuid("26acc49a-f046-455f-a215-2a18525fc7bc");
        dd(123);*/

        $user = auth()->user();
        $token = $request->bearerToken();
        $tokenData = SystemUsersToken::where('token_value', $token)->first();

        if (!$tokenData) {
            throw new CustomException('Токен не найден', 404);
        }

        $participationId = $tokenData->participation_id;
        $userParticipationInfo = DataUsersParticipations::userParticipationInfo($participationId);

        $data = collect([
            'user' => $user,
            'user_id' => $user->user_id,
            'user_participation_info' => $userParticipationInfo,
            'company_data' => DataCompaniesLocations::find($userParticipationInfo['company_location_id'])->company ?? "",
            'region_data' => DirectoryCountriesRegion::find($userParticipationInfo['region_id']),
            'district_data' => DirectoryCountriesRegionsDistrict::find($userParticipationInfo['district_id']),
            'role_data' => SystemRoles::find($userParticipationInfo['role_id']),
            'participation_id' => $participationId,
            'status' => true,
            'message' => '',
            'response_resource_data' => SvrApiAuthInfoResource::class,
            'response_resource_dictionary' => false,
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Авторизация пользователя.
     *
     * @param Request $request HTTP запрос с данными для авторизации.
     *
     * @return JsonResponse|SvrApiResponseResource Возвращает ресурс с данными авторизации или JSON ответ с ошибкой.
     * @throws CustomException
     */
    public function authLogin(Request $request): SvrApiResponseResource|JsonResponse
    {
        $model = new SystemUsers();
        $filterKeys = ['user_email', 'user_password'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);

        $credentials = $request->only($filterKeys);

        $users = SystemUsers::where([
            ['user_email', '=', $credentials['user_email']],
            ['user_status', '=', SystemStatusEnum::ENABLED->value],
            ['user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value],
        ])->get();

        $user = $users->first(fn($item) => Hash::check($credentials['user_password'], $item->user_password));

        if (!$user) {
            throw new CustomException('Неправильный логин или пароль', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $lastToken = SystemUsersToken::userLastTokenData($user->user_id);

        $participationId = $lastToken ? $lastToken->participation_id : $this->getParticipationId($user);

        if (!$participationId) {
            throw new CustomException('Пользователь не привязан ни к одному хозяйству/району/региону', 401);
        }

        (new SystemUsersToken())->userTokenStore([
            'user_id' => $user->user_id,
            'participation_id' => $participationId,
            'token_value' => $token,
            'token_client_ip' => $request->ip()
        ]);

        $data = $this->prepareAuthData($user, $token, $participationId);

        return new SvrApiResponseResource($data);
    }

    /**
     * Выход пользователя.
     *
     * @param Request $request HTTP запрос с токеном авторизации.
     *
     * @return JsonResponse Возвращает JSON ответ с подтверждением выхода.
     */
    public function authLogout(Request $request): JsonResponse
    {
        $user = auth()->user();

        SystemUsersToken::where([
            ['user_id', '=', $user->user_id],
            ['token_value', '=', $user->token]
        ])->update(['token_status' => SystemStatusEnum::DISABLED->value]);

        $user->tokens()->delete();

        return response()->json(['message' => 'user logged out'], 200);
    }

    /**
     * Установить (выбрать) привязку к компании, региону, району.
     *
     * @param Request $request HTTP запрос с данными привязки.
     *
     * @return SvrApiResponseResource Возвращает ресурс с данными привязки.
     */
    public function authSet(Request $request): SvrApiResponseResource
    {
        $model = new DataUsersParticipations();
        $filterKeys = ['participation_item_id', 'participation_item_type'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);

        $rules['participation_type'] = $rules['participation_item_type'];
        $rules['participation_item_id'] = "required|" . $rules['participation_item_id'];
        $messages['participation_type'] = $messages['participation_item_type'];

        unset($rules['participation_item_type'], $messages['participation_item_type']);

        $request->validate($rules, $messages);

        $user = auth()->user();
        $participation = $this->getParticipation($user, $request);

        if ($participation) {
            $request->merge(['participation_item_type' => $request->query('participation_type')]);
            SystemUsersToken::where('token_value', $user->token)->update(['participation_id' => $participation->participation_id]);
        }

        $responseMessage = $participation ? 'Привязка успешно установлена' : 'Нет доступа к запрошенной привязке';

        $data = $this->prepareAuthSetData($user, $responseMessage, $participation);

        return new SvrApiResponseResource($data);
    }

    /**
     * Редактирование реквизитов для подключения к хорриоту.
     *
     * @param Request $request HTTP запрос с данными реквизитов.
     *
     * @return SvrApiResponseResource Возвращает ресурс с подтверждением обновления реквизитов.
     */
    public function authHerriotRequisites(Request $request): SvrApiResponseResource
    {
        $model = new SystemUsers();
        $filterKeys = ['user_herriot_apikey', 'user_herriot_issuerid', 'user_herriot_login', 'user_herriot_password'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);

        $rules['herriot_api_key'] = "required|" . $rules['user_herriot_apikey'];
        $rules['herriot_issuer_id'] = "required|" . $rules['user_herriot_issuerid'];
        $rules['herriot_login'] = "required|" . $rules['user_herriot_login'];
        $rules['herriot_password'] = "required|" . $rules['user_herriot_password'];

        $messages['herriot_api_key'] = $messages['user_herriot_apikey'];
        $messages['herriot_issuer_id'] = $messages['user_herriot_issuerid'];
        $messages['herriot_login'] = $messages['user_herriot_login'];
        $messages['herriot_password'] = $messages['user_herriot_password'];

        unset($rules['user_herriot_apikey'], $rules['user_herriot_issuerid'], $rules['user_herriot_login'], $rules['user_herriot_password']);
        unset($messages['user_herriot_apikey'], $messages['user_herriot_issuerid'], $messages['user_herriot_login'], $messages['user_herriot_password']);

        $request->validate($rules, $messages);

        $this->updateSystemSetting('herriot_api_key', $request->query('herriot_api_key'));
        $this->updateSystemSetting('herriot_issuer_id', $request->query('herriot_issuer_id'));
        $this->updateSystemSetting('herriot_login', $request->query('herriot_login'));
        $this->updateSystemSetting('herriot_password', $request->query('herriot_password'));

        $user = auth()->user();

        $data = collect([
            'user_id' => $user->user_id,
            'status' => true,
            'message' => 'Реквизиты установлены',
            'response_resource_data' => AuthHerriotRequisitesDataResource::class,
            'response_resource_dictionary' => false,
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Получить идентификатор привязки пользователя.
     *
     * @param SystemUsers $user Пользователь.
     *
     * @return int|null Идентификатор привязки или null, если привязка не найдена.
     */
    private function getParticipationId(SystemUsers $user): ?int
    {
        $participation = DataUsersParticipations::where([
            ['user_id', '=', $user->user_id],
            ['participation_status', '=', SystemStatusEnum::ENABLED->value]
        ])->latest('updated_at')->first();

        return $participation ? $participation->participation_id : null;
    }

    /**
     * Подготовить данные для авторизации.
     *
     * @param SystemUsers $user Пользователь.
     * @param string $token Токен авторизации.
     * @param int $participationId Идентификатор привязки.
     *
     * @return Collection Данные для ресурса авторизации.
     */
    private function prepareAuthData(SystemUsers $user, string $token, int $participationId): Collection
    {
        return collect([
            'user_token' => $token,
            'user' => $user,
            'user_participation_info' => DataUsersParticipations::userParticipationInfo($participationId),
            'user_roles_list' => SystemUsersRoles::userRolesList($user->user_id)->all(),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($user->user_id)->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($user->user_id)->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($user->user_id)->all(),
            'avatars' => $user->getCurrentUserAvatar($user->user_id),
            'user_id' => $user->user_id,
            'status' => true,
            'message' => '',
            'response_resource_data' => AuthInfoSystemUsersDataResource::class,
            'response_resource_dictionary' => AuthInfoSystemUsersDictionaryResource::class,
        ]);
    }

    /**
     * Получить привязку пользователя.
     *
     * @param SystemUsers $user Пользователь.
     * @param Request $request HTTP запрос с данными привязки.
     *
     * @return mixed Привязка пользователя или null, если привязка не найдена.
     */
    private function getParticipation(SystemUsers $user, Request $request)
    {
        return DB::table((new DataUsersParticipations())->getTable() . ' as dup')
            ->select('dup.*')
            ->leftJoin(SystemUsers::getTableName() . ' AS su', 'su.user_id', '=', 'dup.user_id')
            ->leftJoin(SystemUsersToken::getTableName() . ' AS sut', 'sut.user_id', '=', 'sut.user_id')
            ->where([
                ['su.user_id', '=', $user->user_id],
                ['sut.token_value', '=', $user->token],
                ['dup.participation_item_id', '=', $request->query('participation_item_id')],
                ['dup.participation_item_type', '=', $request->query('participation_type')]
            ])
            ->first();
    }

    /**
     * Подготовить данные для установки привязки.
     *
     * @param SystemUsers $user Пользователь.
     * @param string $responseMessage Сообщение ответа.
     * @param mixed $participation Привязка пользователя.
     *
     * @return Collection Данные для ресурса установки привязки.
     */
    private function prepareAuthSetData(SystemUsers $user, string $responseMessage, mixed $participation): Collection
    {
        return collect([
            'user_token' => $user->token,
            'user' => $user,
            'user_participation_info' => DataUsersParticipations::userParticipationInfo($user->participation_id),
            'user_roles_list' => SystemUsersRoles::userRolesList($user->user_id)->all(),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($user->user_id)->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($user->user_id)->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($user->user_id)->all(),
            'avatars' => $user->getCurrentUserAvatar($user->user_id),
            'user_id' => $user->user_id,
            'status' => !is_null($participation),
            'user_companies_count' => DataUsersParticipations::getUsersCompaniesCount($user->user_id),
            'message' => $responseMessage,
            'response_resource_data' => AuthSetSystemUsersDataResource::class,
            'response_resource_dictionary' => AuthInfoSystemUsersDictionaryResource::class,
        ]);
    }

    /**
     * Обновить системную настройку.
     *
     * @param string $settingCode Код настройки.
     * @param string $settingValue Значение настройки.
     */
    private function updateSystemSetting(string $settingCode, string $settingValue): void
    {
        SystemSetting::where('setting_code', $settingCode)->update(['setting_value' => $settingValue]);
    }
}
