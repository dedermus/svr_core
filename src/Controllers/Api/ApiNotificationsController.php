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
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemSetting;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthDataResource;
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

class ApiNotificationsController extends Controller
{
    /**
     * Получение информации о пользователе.
     *
     * @param Request $request HTTP запрос с токеном авторизации.
     *
     * @return SvrApiResponseResource|JsonResponse Возвращает ресурс с данными пользователя или JSON ответ с ошибкой.
     * @throws CustomException
     */
    public function notificationsData(Request $request, $notification_id): SvrApiResponseResource|JsonResponse
    {
        $request->merge(['notification_id' => $notification_id]);
        $this->validateNotificationRequest($request, ['notification_id']);

        $notification = SystemUsersNotifications::find($request->notification_id);

        if (!$notification) {
            throw new CustomException('Пользователь не найден', 404);
        }
        $user = $this->getUser($notification->user_id);
        $data = $this->prepareUserData($user);


        return new SvrApiResponseResource($data);
    }

    /**
     * Валидация запроса по уведомлению.
     *
     * @param Request $request    HTTP запрос с данными уведомления.
     * @param array   $filterKeys Ключи для фильтрации данных запроса.
     *
     * @return void
     */
    private function validateNotificationRequest(Request $request, array $filterKeys): void
    {
        $model = new SystemUsersNotifications();
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $rules['notification_id'][0] = 'required';
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
        return collect([
            'user_id' => $user->user_id,
            'status' => true,
            'data' => [
                '4545' => '34534'
            ],
            'message' => 'Реквизиты установлены',
            'response_resource_data' => AuthHerriotRequisitesDataResource::class,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => 1,
                'max_page' => 1,
                'cur_page' => 1,
                'per_page' => 1
            ],
        ]);
    }
}
