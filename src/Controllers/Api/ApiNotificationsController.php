<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemParticipationsTypesEnum;
use Svr\Core\Exceptions\CustomException;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Models\SystemUsersNotificationsMessages;
use Svr\Core\Resources\NotificationsDataResource;
use Svr\Core\Resources\SvrApiResponseResource;
use Svr\Data\Models\DataApplications;
use Svr\Data\Models\DataUsersParticipations;

class ApiNotificationsController extends Controller
{
    protected SystemUsersNotifications $systemUsersNotifications;
    protected SystemUsers $systemUsers;
    protected string $order_field = 'notification_id';  // поле для сортировки по умолчанию
    public function __construct(SystemUsersNotifications $systemUsersNotifications, SystemUsers $systemUsers)
    {
        $this->systemUsersNotifications = $systemUsersNotifications;
        $this->systemUsers = $systemUsers;
    }

    /**
     * Получение информации об уведомлении пользователя по NOTIFICATION_ID.
     *
     * @param Request $request HTTP запрос с токеном авторизации.
     * @param int $notification_id Идентификатор уведомления.
     *
     * @return SvrApiResponseResource|JsonResponse
     * @throws CustomException
     */
    public function notificationsData(Request $request, int $notification_id): SvrApiResponseResource|JsonResponse
    {
        $request->merge(['notification_id' => $notification_id]);
        $this->validateNotificationRequest($request, ['notification_id']);

        $notification = $this->systemUsersNotifications->find($notification_id);
        if (!$notification) {
            throw new CustomException('Уведомление не найдено', 404);
        }

        $user = $this->systemUsers->getUser(optional($notification)->user_id);
        $this->updateNotificationDateView($notification_id);
        $data = $this->prepareResponseData($notification, $user);




        $notification_type = 'application_animal_add';
        $company_id = 2;
        $user_id = 1;
        $notification_data = DataApplications::find(45)->toArray();
       // dd($notification_type, $company_id, $user_id, $notification_data);
        $notification_data = [
            'application_id' => 8
        ];
        $this->systemUsersNotifications->notificationCreate($notification_type, $company_id, $user_id, $notification_data);


        $per_page = 2000;
        $cur_page = 1;
        $filter = [
              "user_id" => 2,
              "user_full_name" => "Алексан",
             "district_id" => [1, 2, 62, 1717],
              "region_id" => [47],
                    'sdfsdfds' => 'sdd',
              "user_date_block_max" => "12.07.2021"
        ];

        //$this->systemUsers->users_list($per_page, $cur_page, false, $filter, '');

        return new SvrApiResponseResource($data);
    }

    /**
     * Получение списка уведомлений пользователя.
     *
     * @param Request $request HTTP запрос с токеном авторизации.
     *
     * @return SvrApiResponseResource|JsonResponse
     */
    public function notificationsList(Request $request): SvrApiResponseResource|JsonResponse
    {
        $user = auth()->user();
        $user_id = optional($user)->user_id;
        $request->merge(['user_id' => $user_id]);
        $this->validateNotificationListRequest($request, ['cur_page', 'per_page', 'order_field', 'order_direction']);
        $notifications = $this->getUserNotificationsPage($user_id);
        $data = $this->prepareResponseData($notifications, $user);

        return new SvrApiResponseResource($data);
    }

    /**
     * Пометить все уведомления пользователя как прочитанные.
     *
     * @param Request $request HTTP запрос с токеном авторизации.
     *
     * @return SvrApiResponseResource|JsonResponse
     */
    public function notificationsReadAll(Request $request): SvrApiResponseResource|JsonResponse
    {
        $user = auth()->user();
        $user_id = optional($user)->user_id;
        $request->merge(['user_id' => $user_id]);
        $this->systemUsersNotifications->notificationReadAll($user_id);
        $data = $this->prepareResponseData(null, $user);
        $data['message'] = 'Уведомления прочитаны';

        return new SvrApiResponseResource($data);
    }

    /**
     * Валидация запроса на получение списка уведомлений пользователя.
     *
     * @param Request $request HTTP запрос с данными уведомления.
     * @param array $filterKeys Ключи для фильтрации данных запроса.
     *
     * @return void
     */
    private function validateNotificationListRequest(Request $request, array $filterKeys): void
    {
        $rules = [
            'cur_page' => 'nullable|numeric|min_digits:1|max_digits:9',
            'per_page' => 'nullable|numeric|min_digits:1|max_digits:9',
            'order_field' => [
                'nullable',
                Rule::in([
                    'notification_id',
                    'user_id',
                    'author_id',
                    'notification_type',
                    'notification_title',
                    'notification_text',
                    'notification_date_add',
                    'notification_date_view'
                ])
            ],
            'order_direction' => [
                'nullable',
                Rule::in(['asc','desc']),
            ],
        ];
        $messages = $this->systemUsersNotifications->getFilterValidationMessages($filterKeys);

        $messages = array_merge($messages, [
            'cur_page' => trans('svr-core-lang::validation'),
            'per_page' => trans('svr-core-lang::validation'),
            'order_field' => trans('svr-core-lang::validation'),
            'order_direction' => trans('svr-core-lang::validation'),
        ]);
        $request->validate($rules, $messages);
    }

    /**
     * Валидация запроса по уведомлению.
     *
     * @param Request $request HTTP запрос с данными уведомления.
     * @param array $filterKeys Ключи для фильтрации данных запроса.
     *
     * @return void
     */
    private function validateNotificationRequest(Request $request, array $filterKeys): void
    {
        $rules = $this->systemUsersNotifications->getFilterValidationRules($request, $filterKeys);
        $messages = $this->systemUsersNotifications->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);
    }

    /**
     * Обновить дату просмотра уведомления.
     *
     * @param int $notification_id
     * @return void
     */
    private function updateNotificationDateView(int $notification_id): void
    {
        $this->systemUsersNotifications->notificationDateViewUpdate($notification_id);
    }

    /**
     * Получить коллекцию уведомлений пользователя c пагинацией.
     * @param $user_id          - пользователь USER_ID
     *
     * @return array
     */
    private function getUserNotificationsPage($user_id): array
    {
        $per_page = Config::get('per_page');
        $cur_page = Config::get('cur_page');
        $order_field = (!is_null(Config::get('order_field'))) ? Config::get('order_field') : $this->order_field;
        $order_direction = Config::get('order_direction');
        return $this->systemUsersNotifications->getUserNotificationsPage($user_id, $per_page, $cur_page, $order_field, $order_direction);
    }

    /**
     * Подготовить данные для ответа.
     *
     * @param $notification
     * @param $user
     * @return Collection
     */
    private function prepareResponseData($notification, $user): Collection
    {

        $count = ((isset($notification['results'])) ? count($notification['results'])
            : (isset($notification['notification_id']))) ? 1 : 0;
        $message = ($count > 0) ? '' : 'Данные не найдены';
        return collect([
            'user_id' => optional($user)->user_id,
            'notification' => (isset($notification['results'])) ? $notification['results'] : $notification,
            'status' => true,
            'message' => $message,
            'response_resource_data' => NotificationsDataResource::class,
            'response_resource_dictionary' => false,
        ]);
    }
}
