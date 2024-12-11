<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Svr\Core\Exceptions\CustomException;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Resources\NotificationsDataResource;
use Svr\Core\Resources\SvrApiResponseResource;

class ApiNotificationsController extends Controller
{
    protected SystemUsersNotifications $systemUsersNotifications;
    protected SystemUsers $systemUsers;
    protected int $cur_page = 1;                        // текущая страница
    protected int $per_page = 100;                      // максимальное количество записей на странице
    protected int $total_records = 0;                   // всего записей
    protected int $max_page = 1;                        // максимальное число страниц
    protected string $order_field = 'notification_id';  // поле для сортировки
    protected string $order_direction = 'desc';         // направление сортировки

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
        $this->per_page = (isset($request->per_page)) ? $request->per_page : $this->per_page;
        $this->cur_page = (isset($request->cur_page)) ? $request->cur_page : $this->cur_page;
        if (!$notification) {
            throw new CustomException('Уведомление не найдено', 404);
        }

        $user = $this->systemUsers->getUser(optional($notification)->user_id);
        $notifications_page = $this->getUserNotificationsPage(optional($user)->user_id, $this->per_page, $this->cur_page, $this->order_field, $this->order_direction);
        $this->total_records = $notifications_page['total'];
        $this->updateNotificationDateView($notification_id);
        $data = $this->prepareResponseData($notification, $user);
        $notification_message_data = [
            'message_id' => 12,
            'notification_type' => 'integration_selex_guid_good',
            'message_description' => 'Отправка GUID в СЕЛЕКС - УСПЕХ',
            'message_title_front' => 'Передача уникального номера из СВР в СЕЛЭКС',
            'message_title_email' => NULL,
            'message_text_front' => 'Передача уникальных номеров из СВР в ИАС «СЕЛЭКС» завершена. Передано {{animals_count_total}} записе…',
            'message_text_email' => NULL,
            'message_status_front' => 'enabled',
            'message_status_email' => 'disabled',
            'message_status' => 'enabled',
            'created_at' => '2024-11-18 13:00:16',
            'updated_at' => '2024-11-18 13:00:16',
        ];

        $this->systemUsersNotifications->notifications_send_user($user, $notification_message_data);

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
        $request->merge(['user_id' => optional($user)->user_id]);
        $this->validateNotificationListRequest($request, ['cur_page', 'per_page', 'order_field', 'order_direction']);
        $this->per_page = (isset($request->per_page)) ? $request->per_page : $this->per_page;
        $this->cur_page = (isset($request->cur_page)) ? $request->cur_page : $this->cur_page;
        $this->order_field = (isset($request->order_field)) ? $request->order_field : $this->order_field;
        $this->order_direction = (isset($request->order_direction)) ? $request->order_direction : $this->order_direction;
        $notifications = $this->getUserNotificationsPage(optional($user)->user_id, $this->per_page, $this->cur_page, $this->order_field, $this->order_direction);
        $this->total_records =$notifications['total'];
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
        $request->merge(['user_id' => optional($user)->user_id]);
        $this->systemUsersNotifications->notificationReadAll(optional($user)->user_id);
        $this->per_page = (isset($request->per_page)) ? $request->per_page : $this->per_page;
        $this->cur_page = (isset($request->cur_page)) ? $request->cur_page : $this->cur_page;
        $this->order_field = (isset($request->order_field)) ? $request->order_field : $this->order_field;
        $this->order_direction = (isset($request->order_direction)) ? $request->order_direction : $this->order_direction;
        $notifications = $this->getUserNotificationsPage(optional($user)->user_id, $this->per_page, $this->cur_page, $this->order_field, $this->order_direction);
        $notifications['results'] = null;
        $this->total_records =$notifications['total'];
        $data = $this->prepareResponseData($notifications, $user);
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
     *
     * @param $user_id          - пользователь USER_ID
     * @param $per_page         - текущая страница
     * @param $cur_page         - максимальное количество записей на странице
     * @param $order_field      - поле сортировки, строка, 50 символов
     * @param $order_direction  - направление сортировки, desc/asc
     *
     * @return array
     */
    private function getUserNotificationsPage($user_id, $per_page, $cur_page, $order_field, $order_direction): array
    {
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
        return collect([
            'user_id' => optional($user)->user_id,
            'notification' => (isset($notification['results'])) ? $notification['results'] : $notification,
            'status' => true,
            'message' => '',
            'response_resource_data' => NotificationsDataResource::class,
            'response_resource_dictionary' => false,
            'pagination' => [
                'total_records' => $this->total_records,
                'cur_page' => ($this->total_records == 0) ? $this->total_records : $this->cur_page,
                'per_page' => ($this->total_records == 0) ? $this->total_records : $this->per_page,
            ],
        ]);
    }
}
