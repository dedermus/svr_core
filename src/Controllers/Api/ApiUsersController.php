<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthDataResource;
use Svr\Core\Resources\AuthInfoSystemUsersDictionaryResource;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataUsersParticipations;

class ApiUsersController extends Controller
{
    /**
     * Получить информацию о пользователе
     *
     * @param Request $request
     *
     * @return JsonResponse|SvrApiResponseResource
     */
    public function usersData(Request $request): SvrApiResponseResource|JsonResponse
    {
        $model = new SystemUsers();
        $filterKeys = ['user_id'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $rules['user_id'][0] = 'required';
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);

        // Проверить существование пользователя, по которому мы выдадим информацию
        /** @var SystemUsers $user */
        $user = SystemUsers::where([
            ['user_id', '=', $request->user_id],
        ])->first()->toArray();

        $message = is_null($user)
            ? 'Пользователь не найден'
            : '';
        $user_id = (isset($user['user_id'])) ? $user['user_id'] : null;
        $token_data = SystemUsersToken::userLastTokenData($user_id);
        $participation_id = (isset($token_data['participation_id'])) ? $token_data['participation_id'] : null;
        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);

        // Подготовили данные для передачи в ресурс
        $data = collect([
            'user_id' => $user_id,
            'user' => $user,
            'user_participation_info' => $user_participation_info,
            'user_companies_count' => DataUsersParticipations::getUsersCompaniesCount($user_id),
            'user_roles_list' => SystemUsersRoles::userRolesList($user_id),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($user_id)->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($user_id)->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($user_id)->all(),
            'avatars' => (new SystemUsers())->getCurrentUserAvatar($user_id),
            'status' => true,
            'message' => $message,
            'response_resource_data' => AuthDataResource::class,
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
     * @param Request $request
     *
     * @return SvrApiResponseResource
     */
    public function usersEdit(Request $request): SvrApiResponseResource
    {
        $model = new SystemUsers();
        $filterKeys = ['user_id', 'user_first', 'user_middle', 'user_last', 'user_email', 'user_phone'];

        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $rules['user_id'][0] = 'required';
        $rules['user_first'] = str_replace("nullable", "required", $rules['user_first']);
        $rules['user_middle'] = str_replace("nullable", "required", $rules['user_middle']);
        $rules['user_last'] = str_replace("nullable", "required", $rules['user_last']);
        $rules['user_email'] = 'required|'.$rules['user_email'];
        $messages = $model->getFilterValidationMessages($filterKeys);

        $request->validate($rules, $messages);

        // обновим данные по пользователю
        $model->find($request->user_id);
        $model->update($request->toArray());

        // получить пользователя, по которому мы выдадим информацию
        /** @var SystemUsers $user */
        $user = SystemUsers::where([
            ['user_id', '=', $request->user_id],
        ])->first()->toArray();

        $user_id = (isset($user['user_id'])) ? $user['user_id'] : null;
        $token_data = SystemUsersToken::userLastTokenData($user_id);
        $participation_id = (isset($token_data['participation_id'])) ? $token_data['participation_id'] : null;
        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);

        // Подготовили данные для передачи в ресурс
        $data = collect([
            'user_id' => $user_id,
            'user' => $user,
            'user_participation_info' => $user_participation_info,
            'user_companies_count' => DataUsersParticipations::getUsersCompaniesCount($user_id),
            'user_roles_list' => SystemUsersRoles::userRolesList($user_id),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($user_id)->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($user_id)->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($user_id)->all(),
            'avatars' => (new SystemUsers())->getCurrentUserAvatar($user_id),
            'status' => true,
            'message' => '',
            'response_resource_data' => AuthDataResource::class,
            'response_resource_dictionary' => AuthInfoSystemUsersDictionaryResource::class,
            'pagination' => [
                'total_records' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);

        return new SvrApiResponseResource($data);
    }
}
