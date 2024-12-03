<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
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
    /**
     * Добавить реквизиты Хорриота пользователю
     *
     * @param Request $request
     *
     * @return SvrApiResponseResource Возвращает ресурс с данными пользователя
     */
    public function userHerriotReqAdd(Request $request): SvrApiResponseResource
    {
        $this->validateHerriotReqAddRequest($request);
        $user = auth()->user();

        SystemUsers::where('user_id', $user->user_id)
            ->update($request->all());

        $data = $this->prepareUserData($user);
        $data['message'] = 'Логин и пароль Хорриота установлены';
        return new SvrApiResponseResource($data);
    }

    /**
     * Валидация запроса на добавление аватара.
     *
     * @param Request $request HTTP запрос с данными реквизитов Хорриота.
     *
     * @return void
     */
    private function validateHerriotReqAddRequest(Request $request): void
    {
        $model = new SystemUsers();
        $filterKeys = ['user_herriot_login', 'user_herriot_password',
                       'user_herriot_web_login', 'user_herriot_apikey',
                       'user_herriot_issuerid', 'user_herriot_serviceid'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);

        $rules['user_herriot_login'] = "required|" . $rules['user_herriot_login'];
        $rules['user_herriot_password'] = "required|" . $rules['user_herriot_password'];
        $rules['user_herriot_web_login'] = "required|" . $rules['user_herriot_web_login'];
        $rules['user_herriot_apikey'] = "required|" . $rules['user_herriot_apikey'];
        $rules['user_herriot_issuerid'] = "required|" . $rules['user_herriot_issuerid'];
        $rules['user_herriot_serviceid'] = "required|" . $rules['user_herriot_serviceid'];

        $request->validate($rules, $messages);
    }

    /**
     * Удаление аватара у пользователя
     * @param Request $request
     *
     * @return SvrApiResponseResource|Collection Возвращает ресурс с данными пользователя или коллекцию с сообщением об ошибке.
     */
    public function userAvatarDelete(Request $request): SvrApiResponseResource|Collection
    {
        $this->validateAvatarDeleteRequest($request);

        $user = $this->getUser($request->user_id);

        if (!$user) {
            return $this->createResponse(false, 'Пользователь не найден');
        }

        $user->deleteAvatar($request);
        SystemUsers::where('user_id', $user->user_id)
            ->update(['user_avatar' => null]);
        $user = $this->getUser($request->user_id);
        $data = $this->prepareUserData($user);
        $data['message'] = 'Аватар успешно удалён';

        return new SvrApiResponseResource($data);
    }

    /**
     * Валидация запроса на удаление аватара.
     *
     * @param Request $request HTTP запрос с данными.
     *
     * @return void
     */
    private function validateAvatarDeleteRequest(Request $request): void
    {
        $model = new SystemUsers();
        $rules = $model->getFilterValidationRules($request, ['user_id']);
        $messages = $model->getFilterValidationMessages(['user_id']);

        $rules['user_id'][0] = 'required';

        $request->validate($rules, $messages);
    }

    /**
     * Изменение аватара пользователя.
     *
     * @param Request $request HTTP запрос, содержащий ID пользователя и данные аватара.
     *
     * @return SvrApiResponseResource|Collection Возвращает ресурс с данными пользователя или коллекцию с сообщением об ошибке.
     */
    public function userAvatarAdd(Request $request): SvrApiResponseResource|Collection
    {
        $this->validateAvatarAddRequest($request);

        $user = $this->getUser($request->user_id);

        if (!$user) {
            return $this->createResponse(false, 'Пользователь не найден');
        }

        SystemUsers::where('user_id', $user->user_id)
            ->update(['user_avatar' => $user->addFileAvatar($request)]);

        $user = $this->getUser($request->user_id);
        $data = $this->prepareUserData($user);
        $data['message'] = 'Аватар успешно обновлен';

        return new SvrApiResponseResource($data);
    }

    /**
     * Валидация запроса на добавление аватара.
     *
     * @param Request $request HTTP запрос с данными аватара.
     *
     * @return void
     */
    private function validateAvatarAddRequest(Request $request): void
    {
        $model = new SystemUsers();
        $rules = $model->getFilterValidationRules($request, ['user_id', 'user_avatar']);
        $messages = $model->getFilterValidationMessages(['user_id', 'user_avatar']);

        $rules['user_id'][0] = 'required';
        $rules['user_avatar'] = 'required|' . $rules['user_avatar'];

        $request->validate($rules, $messages);
    }

    /**
     * Изменение пароля пользователя.
     *
     * @param Request $request HTTP запрос с данными для изменения пароля.
     *
     * @return Collection Возвращает коллекцию с сообщением об успешном изменении пароля или ошибке.
     */
    public function userPasswordChange(Request $request): Collection
    {
        $this->validatePasswordChangeRequest($request);

        $user = $this->getUser($request->user_id);

        if (!$user) {
            return $this->createResponse(false, 'Пользователь не найден');
        }

        if ($request->password !== $request->password_confirmation) {
            return $this->createResponse(false, 'Пароли не совпадают');
        }

        if (!Hash::check($request->current_password, $user->user_password)) {
            return $this->createResponse(false, 'Не верный текущий пароль');
        }

        $user->update(['user_password' => Hash::make($request->password)]);
        return $this->createResponse(true, 'Пароль успешно изменен');
    }

    /**
     * Получить информацию о пользователе.
     *
     * @param Request $request HTTP запрос с ID пользователя.
     *
     * @return SvrApiResponseResource|JsonResponse Возвращает ресурс с данными пользователя или JSON ответ с ошибкой.
     */
    public function usersData(Request $request): SvrApiResponseResource|JsonResponse
    {
        $this->validateUserRequest($request, ['user_id']);

        $user = $this->getUser($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $data = $this->prepareUserData($user);

        return new SvrApiResponseResource($data);
    }

    /**
     * Редактировать информацию о пользователе.
     *
     * @param Request $request HTTP запрос с данными для редактирования пользователя.
     *
     * @return SvrApiResponseResource Возвращает ресурс с обновленными данными пользователя.
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
     * Валидация запроса на изменение пароля пользователя.
     *
     * @param Request $request HTTP запрос с данными для изменения пароля.
     *
     * @return void
     */
    private function validatePasswordChangeRequest(Request $request): void
    {
        $model = new SystemUsers();
        $rules = $model->getFilterValidationRules($request, ['user_id', 'user_password']);
        $messages = $model->getFilterValidationMessages(['user_password']);

        $rules['user_id'][0] = 'required';
        $rules['current_password'] = $rules['user_password'];
        $rules['password'] = $rules['user_password'];
        $rules['password_confirmation'] = $rules['user_password'];

        $messages['current_password'] = $messages['user_password'];
        $messages['password'] = $messages['user_password'];
        $messages['password_confirmation'] = $messages['user_password'];

        unset($rules['user_password'], $messages['user_password']);

        $request->validate($rules, $messages);
    }

    /**
     * Валидация запроса пользователя.
     *
     * @param Request $request    HTTP запрос с данными пользователя.
     * @param array   $filterKeys Ключи для фильтрации данных запроса.
     *
     * @return void
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
     * Получить пользователя по ID.
     *
     * @param int $userId Идентификатор пользователя.
     *
     * @return SystemUsers|null Возвращает объект пользователя или null, если пользователь не найден.
     */
    private function getUser(int $userId): ?SystemUsers
    {
        return SystemUsers::where([
            ['user_id', '=', $userId],
            ['user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value],
        ])->first();
    }

    /**
     * Подготовить данные пользователя для ресурса.
     *
     * @param SystemUsers $user Объект пользователя.
     *
     * @return Collection Возвращает коллекцию с данными пользователя.
     */
    private function prepareUserData(SystemUsers $user): Collection
    {
        $userId = $user->user_id;
        $tokenData = SystemUsersToken::userLastTokenData($userId);
        $participationId = $tokenData['participation_id'] ?? null;
        $userParticipationInfo = DataUsersParticipations::userParticipationInfo($participationId);

        return collect([
            'user_id'                       => $userId,
            'user'                          => $user,
            'user_participation_info'       => $userParticipationInfo,
            'user_companies_count'          => DataUsersParticipations::getUsersCompaniesCount($userId),
            'user_roles_list'               => SystemUsersRoles::userRolesList($userId),
            'user_companies_locations_list' => DataUsersParticipations::userCompaniesLocationsList($userId)->all(),
            'user_regions_list'             => DataUsersParticipations::userRegionsList($userId)->all(),
            'user_districts_list'           => DataUsersParticipations::userDistrictsList($userId)->all(),
            'avatars'                       => $user->getCurrentUserAvatar($userId),
            'status'                        => true,
            'message'                       => '',
            'response_resource_data'        => AuthDataResource::class,
            'response_resource_dictionary'  => AuthInfoSystemUsersDictionaryResource::class,
            'pagination'                    => [
                'total_records' => 1,
                'max_page'      => 1,
                'cur_page'      => 1,
                'per_page'      => 1
            ],
        ]);
    }

    /**
     * Создание ответа об выполнении запроса.
     *
     * @param bool   $status  Статус выполнения запроса.
     * @param string $message Сообщение о результате выполнения.
     *
     * @return Collection Возвращает коллекцию с данными о статусе и сообщением.
     */
    private function createResponse(bool $status, string $message): Collection
    {
        return collect([
            'status'     => $status,
            'data'       => [],
            'message'    => $message,
            'pagination' => [
                'total_records' => 1,
                'max_page'      => 1,
                'cur_page'      => 1,
                'per_page'      => 1
            ],
        ]);
    }
}
