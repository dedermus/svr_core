<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthDataResource;
use Svr\Core\Resources\AuthInfoSystemUsersDictionaryResource;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataUsersParticipations;

class ApiUsersController extends Controller
{
    public function userPasswordChange(Request $request)
    {
        // валидация запроса
        $model = new SystemUsers();
        $filterKeys = ['user_id', 'user_password'];
        // - собираем набор правил и сообщений
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        $rules['user_id'][0] = 'required';
        $rules['current_password'] = $rules['user_password'];
        $rules['password'] = $rules['user_password'];
        $rules['password_confirmation'] = $rules['user_password'];
        $messages['current_password'] = $messages['user_password'];
        $messages['password'] = $messages['user_password'];
        $messages['password_confirmation'] = $messages['user_password'];
        unset($rules['user_password'], $messages['user_password']);
        // - проверяем запрос на валидацию
        $request->validate($rules, $messages);

        $user = $this->getUser($request->user_id);
        $status = true;
        $message = '';
        if (is_null($user)) {
            $message = 'Пользователь не найден';
            $status = false;
        }


//        $request->validate(
//            [
//                'user_id' 					=> ['required', 'int'],
//                'current_password'			=> ['string_min:1', 'string_max:64'],
//                'password'					=> ['required', 'string_min:1', 'string_max:64'],
//                'password_confirmation'		=> ['string_min:1', 'string_max:64']
//            ]);

        $userId = $user->user_id;
        $tokenData = SystemUsersToken::userLastTokenData($userId);
        $participationId = $tokenData['participation_id'] ?? null;
        $userParticipationInfo = DataUsersParticipations::userParticipationInfo($participationId);

        return collect([
            'user_id' => $userId,
            'user' => $user,
            'user_participation_info' => $userParticipationInfo,
            'user_companies_count' => DataUsersParticipations::getUsersCompaniesCount($userId),
            'user_roles_list' => SystemUsersRoles::userRolesList($userId),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($userId)->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($userId)->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($userId)->all(),
            'avatars' => $user->getCurrentUserAvatar($userId),
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

    /**
     * Получить информацию о пользователе
     *
     * @param Request $request
     * @return JsonResponse|SvrApiResponseResource
     */
    public function usersData(Request $request): SvrApiResponseResource|JsonResponse
    {
        $this->validateUserRequest($request, ['user_id']);

        $user = $this->getUser($request->user_id);

        if (is_null($user)) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $data = $this->prepareUserData($user);

        return new SvrApiResponseResource($data);
    }

    /**
     * Редактировать информацию о пользователе
     *
     * @param Request $request
     * @return SvrApiResponseResource
     */
    public function usersEdit(Request $request): SvrApiResponseResource
    {
        $this->validateUserRequest($request, [
            'user_id', 'user_first', 'user_middle', 'user_last', 'user_email', 'user_phone'
        ]);

        $user = SystemUsers::find($request->user_id);
        $user->update($request->only(['user_first', 'user_middle', 'user_last', 'user_email', 'user_phone']));

        $data = $this->prepareUserData($user);

        return new SvrApiResponseResource($data);
    }

    /**
     * Валидация запроса пользователя
     *
     * @param Request $request
     * @param array $filterKeys
     */
    private function validateUserRequest(Request $request, array $filterKeys): void
    {
        $model = new SystemUsers();
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $rules['user_id'][0] = 'required';
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);
    }

    /**
     * Получить пользователя по ID
     *
     * @param int $userId
     * @return SystemUsers|null
     */
    private function getUser(int $userId): ?SystemUsers
    {
        return SystemUsers::where([
            ['user_id', '=', $userId],
            ['user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value],
        ])->first();
    }

    /**
     * Подготовить данные пользователя для ресурса
     *
     * @param SystemUsers $user
     * @return Collection
     */
    private function prepareUserData(SystemUsers $user): Collection
    {
        $userId = $user->user_id;
        $tokenData = SystemUsersToken::userLastTokenData($userId);
        $participationId = $tokenData['participation_id'] ?? null;
        $userParticipationInfo = DataUsersParticipations::userParticipationInfo($participationId);

        return collect([
            'user_id' => $userId,
            'user' => $user,
            'user_participation_info' => $userParticipationInfo,
            'user_companies_count' => DataUsersParticipations::getUsersCompaniesCount($userId),
            'user_roles_list' => SystemUsersRoles::userRolesList($userId),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($userId)->all(),
            'user_regions_list' => DataUsersParticipations::userRegionsList($userId)->all(),
            'user_districts_list' => DataUsersParticipations::userDistrictsList($userId)->all(),
            'avatars' => $user->getCurrentUserAvatar($userId),
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
    }
}
