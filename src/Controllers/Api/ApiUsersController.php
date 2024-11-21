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

        $new_user_token_data = ((new SystemUsersToken)->userTokenStore([
            'user_id' => $user['user_id'],
            'participation_id' => $participation_id,
            'token_value' => $token,
            'token_client_ip' => $request->ip()
        ]));

        //(new SystemUsersToken())->userTokenCreate($data);

        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);

        $final_data = [];

        // коллекция привязок ролей к пользователю
        $user_roles_list = SystemUsersRoles::userRolesList($user['user_id'])->all();

        foreach ($user_roles_list as $user_role) {
            $user_role = (array)$user_role;

            $user_role['active'] = $user_role['role_id'] == $user_participation_info['role_id'];

            $final_data['dictionary']['user_roles_list'][$user_role['role_id']] = $user_role;
            $final_data['data']['user_roles_list'][] = $user_role['role_id'];
        }

        // коллекция привязок компаний к пользователю
        $user_companies_locations_list = DataUsersParticipations::userCompaniesLocationsList($user['user_id'])->all();

        foreach ($user_companies_locations_list as $user_company_location) {
            $user_company_location = (array)$user_company_location;

            $user_company_location['active'] = $user_company_location['company_location_id'] == $user_participation_info['company_location_id'];

            $final_data['dictionary']['user_companies_locations_list'][$user_company_location['company_location_id']] = $user_company_location;
            $final_data['data']['user_companies_locations_list'][] = $user_company_location['company_location_id'];
        }

        // коллекция привязок регионов к пользователю
        $user_regions_list = DataUsersParticipations::userRegionsList($user['user_id'])->all();

        foreach ($user_regions_list as $user_region) {
            $user_region = (array)$user_region;

            $user_region['active'] = $user_region['region_id'] == $user_participation_info['region_id'];

            $final_data['dictionary']['user_regions_list'][$user_region['region_id']] = $user_region;
            $final_data['data']['user_regions_list'][] = $user_region['region_id'];
        }

        // коллекция привязок районов к пользователю
        $user_districts_list = DataUsersParticipations::userDistrictsList($user['user_id'])->all();

        foreach ($user_districts_list as $user_district) {
            $user_district = (array)$user_district;

            $user_district['active'] = $user_district['district_id'] == $user_participation_info['district_id'];

            $final_data['dictionary']['user_districts_list'][$user_district['district_id']] = $user_district;
            $final_data['data']['user_districts_list'][] = $user_district['district_id'];
        }

        $avatars = (new SystemUsers())->getCurrentUserAvatar($user['user_id']);

        $final_data['data']['user_id'] = $user['user_id'];
        $final_data['data']['user_token'] = $token;
        $final_data['data']['user_first'] = $user['user_first'];
        $final_data['data']['user_middle'] = $user['user_middle'];
        $final_data['data']['user_last'] = $user['user_last'];
        $final_data['data']['user_avatar_small'] = $avatars['user_avatar_small'];
        $final_data['data']['user_avatar_big'] = $avatars['user_avatar_big'];

        $final_data['status'] = true;
        $final_data['message'] = '';
        $final_data['pagination'] = [
            "total_records" => 0,
            "max_page" => 1,
            "cur_page" => 1,
            "per_page" => 100
        ];

        $final_data['notifications'] = [
            "count_new" => 249,
            "count_total" => 29289
        ];


        //Складываем все данные в объект Collection
        /** @var Collection $data - результирующий объект для вывода через ресурс */
        $data = collect(
            [
                'user_token' => $token,
                'status' => true,
                'message' => '',
                'pagination' => [
                    "total_records" => 0,
                    "max_page" => 1,
                    "cur_page" => 1,
                    "per_page" => 100
                ],
                'user' => $user,
                "user_participation_info" => $user_participation_info,
                "user_roles_list" => $user_roles_list,
                "user_companies_locations_list" => $user_companies_locations_list,
                "user_regions_list" => $user_regions_list,
                "user_districts_list" => $user_districts_list,
                "avatars" => $avatars,
                "notifications" => [
                    "count_new" => SystemUsersNotifications::where([
                        ['user_id', '=', $user['user_id']],
                        ['notification_date_view', '=', null]
                    ])->count(),
                    "count_total" => SystemUsersNotifications::where([
                        ['user_id', '=', $user['user_id']],
                    ])->count(),
                ]
            ]
        );


//        return $final_data;
//        dd($final_data);
        return new AuthInfoSystemUsersResource($data);


//        dd(new AuthInfoSystemUsersResource($data));
//        dd($final_data, $data);


        /*$request->merge([
            'user_id'            => $user->user_id,
            'participation_id'   => $participation_id,
            'role_id'            => $user_role_id,
            'token_value'        => $token,
            'token_client_ip'    => $request->ip(),
            'token_client_agent' => Browser::userAgent(),//$request->header('User-Agent'),
            'browser_name'       => Browser::browserFamily(),
            'browser_version'    => Browser::browserVersion(),
            'platform_name'      => Browser::platformFamily(),
            'platform_version'   => Browser::platformVersion(),
            'device_type'        => strtolower(Browser::deviceType()),
            'token_last_login'   => getdate()[0],
            'token_last_action'  => getdate()[0],
            'token_status'       => SystemStatusEnum::ENABLED->value,
            ...$user->toArray(),
            'created_at'         =>  \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at'         =>  \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s'),
        ]);*/

        return new AuthInfoSystemUsersResource($data);
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
