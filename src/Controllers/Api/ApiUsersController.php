<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthInfoSystemUsersResource;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;

class ApiUsersController extends Controller
{
    /**
     * Создание новой записи.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $model = new SystemUsers();
        $record = $model->userCreate($request);
        return response()->json($record, 201);
    }

    /**
     * Обновление существующей записи.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $record = new SystemUsers();
        $request->setMethod('PUT');
        $record->userUpdate($request);
        return response()->json($record);
    }

    /**
     * Получение списка записей с пагинацией.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15); // Количество записей на странице по умолчанию
        $records = SystemUsers::paginate($perPage);
        return response()->json($records);
    }

    /**
     * Получение информации о пользователе.
     *
     * @return SvrApiResponseResource|JsonResponse
     */
    public function authInfo(Request $request): SvrApiResponseResource|JsonResponse
    {
        /** @var  $user - получим авторизированного пользователя */
        $user = auth()->user();
        //Получим токен текущего пользователья
        $token = $request->bearerToken();
        //Получим данные о токене из базы
        $token_data = SystemUsersToken::where('token_value', '=', $token)->first()->toArray();
        //Если токен не нашелся - уходим
        if (!$token_data || !isset($token_data['participation_id']))
        { //TODO переписать на нормальный структурированный вид после того как сделаем нормальный конструктор вывода
            return response()->json([
                'message' => 'Unauthenticated'
            ], 403);
        }
        //запомнили participation_id
        $participation_id = $token_data['participation_id'];
        //получили привязки пользователя
        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);
        //собрали данные для передачи в ресурс
        $data = collect([
            'user' => $user,
            'user_id' => $user['user_id'],
            'user_participation_info' => $user_participation_info,
            'company_data' => DataCompaniesLocations::find($user_participation_info['company_location_id'])->company,
            'region_data' => DirectoryCountriesRegion::find($user_participation_info['region_id']),
            'district_data' => DirectoryCountriesRegionsDistrict::find($user_participation_info['district_id']),
            'role_data' => SystemRoles::find($user_participation_info['role_id']),
            'participation_id' => $participation_id,
            'status' => true,
            'message' => '',
            'response_resource_data' => 'Svr\Core\Resources\SvrApiAuthInfoResource',
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
     * @param Request $request
     *
     * @return SvrApiResponseResource
     */
    public function authLogin(Request $request)
    {
        $model = new SystemUsers();
        $user = null; // прееменная для пользователя
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
            foreach ($users as &$item) {
                // если email и password совпали
                if ($item && Hash::check($credentials['user_password'], $item->user_password)) {
                    $user = $item;
                    break;  // выйдем из перебора
                }
            }
            unset($item);
        }
        // Если пользователь не найден
        if (is_null($user)) { //TODO переписать на нормальный структурированный вид после того как сделаем нормальный конструктор вывода
            return response()->json(['error' => 'Неправильный логин или пароль'], 401);
        }
        // Выдать токен пользователю
        $token = $user->createToken('auth_token')->plainTextToken;
        // Последний токен пользователя
        $last_token = SystemUsersToken::userLastTokenData($user->user_id);
        // Пытаемся получить participation_id
        $participation_id = null;
        if ($last_token) {
            $last_token = $last_token->toArray();
            $participation_id = $last_token['participation_id'] ?? null;
        } else {
            //TODO: получить какую-нибудь привязку
        }
        //Создали запись в таблице токенов
        $new_user_token_data = ((new SystemUsersToken)->userTokenStore([
            'user_id' => $user['user_id'],
            'participation_id' => $participation_id,
            'token_value' => $token,
            'token_client_ip' => $request->ip()
        ]));
        //Подготовили данные для передачи в ресурс
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
            'response_resource_data' => 'Svr\Core\Resources\AuthInfoSystemUsersDataResource',
            'response_resource_dictionary' => 'Svr\Core\Resources\AuthInfoSystemUsersDictionaryResource',
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }

    /**
     * Return a validation error response.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return JsonResponse
     */
    private function validationErrorResponse($validator): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 401);
    }
}
