<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Svr\Core\Exceptions\CustomException;
use Svr\Core\Extensions\Handler\CrmListFarms;
use Svr\Core\Jobs\ProcessCrmGetListCountries;
use Svr\Core\Jobs\ProcessCrmGetListDistricts;
use Svr\Core\Jobs\ProcessCrmGetListFarms;
use Svr\Core\Jobs\ProcessCrmGetListRegions;
use Svr\Core\Jobs\ProcessCrmGetListUsers;
use Svr\Core\Jobs\ProcessSendingEmail;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Svr\Core\Resources\NotificationsDataResource;
use Svr\Core\Resources\SvrApiResponseResource;

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
        $recorded = Http::recorded();
        // Basic authentication...
//        $response = Http::acceptJson()->post('https://crm.plinor.local/allApi/getToken/', [
//            'email' => 'svr@plinor.ru', 'password' => 'ZmQ0czNlWXpyMDY2bGQwbg=='
//        ]);




//        $response = Http::withUrlParameters([
//            'host' => env('CRM_HOST'),
//            'api' => env('CRM_API'),
//            'endpoint' => env('CRM_END_POINT_TOKEN'),
//        ])->acceptJson()->post('{+host}/{api}/{endpoint}/', [
//            'email' => 'svr@plinor.ru', 'password' => 'ZmQ0czNlWXpyMDY2bGQwbg=='
//        ]);
//        $response = json_decode($response->body(), true);
//        if ($response['data']['token']) {
//            var_export($response['data']['token']); die();
//        }
//dd(json_decode($response->body(), true), $recorded);
//
//       // ProcessCrmGetListFarms::dispatch()
//        ProcessCrmGetToken::dispatch('svr@plinor.ru', 'ZmQ0czNlWXpyMDY2bGQwbg==', );
        ProcessSendingEmail::dispatch('dedermus@gmail.com', 'Это для Вани', 'Привет Мир и ВАня!')->onQueue(env('QUEUE_EMAIL', 'email'));
        ProcessSendingEmail::dispatch('dedermus@gmail.com', 'Это для Вани', 'Привет Мир и ВАня!')->onQueue(env('QUEUE_EMAIL', 'email'));
        ProcessSendingEmail::dispatch('dedermus@gmail.com', 'Это для Вани', 'Привет Мир и ВАня!')->onQueue(env('QUEUE_EMAIL', 'email'));
        //CrmAuth::getToken();
        ProcessCrmGetListUsers::dispatch()->onQueue(env('QUEUE_CRM', 'crm'));
        ProcessCrmGetListFarms::dispatch()->onQueue(env('QUEUE_CRM', 'crm'));
        ProcessCrmGetListCountries::dispatch()->onQueue(env('QUEUE_CRM', 'crm'));
        ProcessCrmGetListRegions::dispatch()->onQueue(env('QUEUE_CRM', 'crm'));
        ProcessCrmGetListDistricts::dispatch()->onQueue(env('QUEUE_CRM', 'crm'));

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
