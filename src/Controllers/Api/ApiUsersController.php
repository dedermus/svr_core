<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthInfoSystemUsersResource;
use Svr\Data\Models\DataUsersParticipations;

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
     * @return AuthInfoSystemUsersResource
     */
    public function show_auth_info()
    {
        /** @var  $user - получим авторизированного пользователя */
        $user = auth()->user();
        //dd($_SERVER);
        dd($user);
        $record = SystemUsers::where('user_id', $user->user_id)->first();
        return new AuthInfoSystemUsersResource($record);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse|AuthInfoSystemUsersResource
     */
    public function authLogin(Request $request)
    {
        $model = new SystemUsers();
        $user = null; // прееменная для пользователя
        $filterKeys = ['user_email', 'user_password'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);

        $credentials = $request->only(['user_email', 'user_password']);

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
        if (is_null($user)) {
            return response()->json(['error' => 'Неправильный логин или пароль'], 401);
        }
        // Выдать токен пользователю
        $token = $user->createToken('auth_token')->plainTextToken;

        $last_token = SystemUsersToken::userLastTokenData($user->user_id);

        $participation_id = null;
        if ($last_token) {
            $last_token = $last_token->toArray();
            $participation_id = $last_token['participation_id'] ?? null;
        } else {
            //TODO: получить какую-нибудь привязку
        }

        (new SystemUsersToken)->userTokenStore([
            'user_id' => $user['user_id'],
            'participation_id' => $participation_id,
            'token_value' => $token,
            'token_client_ip' => $request->ip()
        ]);

        //(new SystemUsersToken())->userTokenCreate($data);

        $user_participation_info = $this->getUserParticipationInfo($participation_id);

        $user_roles_list = $this->getUserRoles($user['user_id'], $user_participation_info);


        $final_data = [];

        // коллекция привязок компаний к пользователю
        $user_companies_locations_list = $this->getUserCompaniesLocationsList($user['user_id'], $user_participation_info);


        // коллекция привязок регионов к пользователю
        $user_regions_list = $this->getUserRegionsList($user['user_id'], $user_participation_info);


        // коллекция привязок районов к пользователю
        $user_districts_list = $this->getUserDistrictsList($user['user_id'], $user_participation_info);

        $avatars = (new SystemUsers())->getCurrentUserAvatar($user['user_id']);

        //Складываем все данные в объект Collection
        /** @var Collection $data - результирующий объект для вывода через ресурс */
        $data = collect(
            [
                'user' => $user,
                "avatars" => $avatars,
                "user_participation_info" => $user_participation_info,
                "user_companies_locations_list" => $user_companies_locations_list,
                "user_roles_list" => $user_roles_list,
                "user_districts_list" => $user_districts_list,
                "user_regions_list" => $user_regions_list,
                'user_token' => $token,
                'status' => true,
                'message' => '',
                'pagination' => [
                    "total_records" => 0,
                    "max_page" => 1,
                    "cur_page" => 1,
                    "per_page" => 100
                ],
                "notifications" => (new SystemUsersNotifications)->getNotificationsCountByUserId($user['user_id']),
            ]
        );

        return new AuthInfoSystemUsersResource($data);
    }

    /**
     * Коллекция привязок ролей к пользователю
     *
     * @param $user_id
     * @param $user_participation_info
     * @return Collection
     */
    private function getUserRoles($user_id, $user_participation_info): Collection
    {

        $user_roles_list = SystemUsersRoles::userRolesList($user_id)->all();

        foreach ($user_roles_list as $user_role) {
            $user_role->active = $user_role->role_id == $user_participation_info->get('role_id');
        }
        return collect($user_roles_list);
    }

    private function getUserParticipationInfo($participation_id): Collection
    {
        return collect(DataUsersParticipations::userParticipationInfo($participation_id));
    }

    /**
     * Коллекция привязок компаний к пользователю
     *
     * @param int        $user_id
     * @param Collection $user_participation_info
     * @return Collection
     */
    private function getUserCompaniesLocationsList(int $user_id, Collection $user_participation_info): Collection
    {

        $user_companies_locations_list = DataUsersParticipations::userCompaniesLocationsList($user_id)->all();

        foreach ($user_companies_locations_list as $user_company_location) {
            $user_company_location->active = $user_company_location->company_location_id == $user_participation_info->get('company_location_id');
        }

        return collect($user_companies_locations_list);
    }

    /**
     * Коллекция привязок регионов к пользователю
     *
     * @param int        $user_id
     * @param Collection $user_participation_info
     * @return Collection
     */
    private function getUserRegionsList(int $user_id, Collection $user_participation_info): Collection
    {
        $user_regions_list = DataUsersParticipations::userRegionsList($user_id)->all();

        foreach ($user_regions_list as $user_region) {
            $user_region->active = $user_region->region_id == $user_participation_info->get('region_id');
        }
        return collect($user_regions_list);
    }

    /**
     * Коллекция привязок районов к пользователю
     *
     * @param int        $user_id
     * @param Collection $user_participation_info
     * @return Collection
     */
    private function getUserDistrictsList(int $user_id, Collection $user_participation_info): Collection
    {
        $user_districts_list = DataUsersParticipations::userDistrictsList($user_id)->all();

        foreach ($user_districts_list as $user_district) {
            $user_district->active = $user_district->district_id == $user_participation_info->get('district_id');
        }
        return collect($user_districts_list);

    }
}
