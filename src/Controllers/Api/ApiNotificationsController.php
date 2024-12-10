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
    protected int $cur_page = 1;
    protected int $per_page = 1;
    protected int $total_records = 1;
    protected int $max_page = 1;



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

        if ($notification) {
            $this->updateNotificationDateView($notification_id);
            $notification['notification_date_view'] = now();
        } else {
            throw new CustomException('Уведомление не найдено', 404);
        }

        $user = $this->systemUsers->getUser(optional($notification)->user_id);
        $data = $this->prepareResponseData($notification, $user);

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
        $order_field = (isset($request->order_field)) ? $request->order_field : 'notification_id';
        $order_direction = (isset($request->order_direction)) ? $request->order_direction : 'desc';
        $notifications = $this->getUserNotificationsPage(optional($user)->user_id, $this->per_page, $this->cur_page, $order_field, $order_direction);
        $this->total_records =$notifications['total'];
        $data = $this->prepareResponseData($notifications, $user);

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
        return $this->systemUsersNotifications->notificationListUserIdPage($user_id, $per_page, $cur_page, $order_field, $order_direction);
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
            'notification' => $notification['results'],
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
