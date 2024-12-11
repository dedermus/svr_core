<?php

namespace Svr\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemNotificationsTypesEnum;
use Svr\Core\Extensions\System\SystemFilter;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Модель Setting
 */
class SystemUsersNotifications extends Model
{
    use GetTableName;
    use HasFactory;
    use GetValidationRules;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_users_notifications';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'notification_id';

    /**
     * Поле даты создания строки
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * Поле даты обновления строки
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Атрибуты, которые можно назначать массово.
     *
     * @var array
     */
    protected $fillable
        = [
            'user_id', //   ID пользователя, чье уведомление
            'author_id', // ID пользователя, создавшего уведомление. Если NULL, то уведомление создал система
            'notification_type', // Тип уведомления
            'notification_title', // Заголовок уведомления
            'notification_text', // Текст уведомления
            'notification_date_add', // Дата создания уведомления
            'notification_date_view', // Дата просмотра уведомления. Если NULL, то уведомление еще не просмотрено
        ];

    /**
     * @var array|string[]
     */
    protected array $dates
        = [
            'notification_date_add', // Дата создания уведомления
            'notification_date_view', // Дата просмотра уведомления. Если NULL, то уведомление еще не просмотрено
            'created_at',                   // Дата создания записи
            'updated_at',                   // Дата редактирования записи
        ];

    /**
     * Формат хранения столбцов даты модели.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var bool
     */
    public $timestamps = true;


    /**
     * Создать запись
     *
     * @param Request $request
     *
     * @return void
     */
    public function userNotificationsCreate(Request $request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }

    /**
     * Обновить запись
     *
     * @param $request
     *
     * @return void
     */
    public function userNotificationsUpdate(Request $request): void
    {
        $this->validateRequest($request);
        $data = $request->all();
        $id = $data[$this->primaryKey] ?? null;

        if ($id) {
            $module = $this->find($id);
            if ($module) {
                $module->update($data);
            }
        }
    }

    /**
     * Обновить дату просмотра уведомления
     *
     * @param $notification_id
     *
     * @return void
     */
    public function notificationDateViewUpdate($notification_id): void
    {
        $module = $this->find($notification_id);
        if ($module) {
            $module->update([
                'notification_date_view' => now()
            ]);
        }
    }

    /**
     * Обновить дату просмотра всех уведомлений пользователя по его USER_ID
     * @param $user_id - пользователь USER_ID
     *
     * @return mixed
     */
    public function notificationReadAll($user_id): mixed
    {
        return SystemUsersNotifications::where('user_id', $user_id)
            ->update([
                'notification_date_view' => now()
            ]);
    }

    /**
     * Получить список уведомлений по USER_ID с пагинацией.
     *
     * @param int $user_id Пользователь USER_ID.
     * @param int $per_page Количество записей на странице.
     * @param int $cur_page Текущая страница.
     * @param string $order_field Поле сортировки, строка, 50 символов.
     * @param string $order_direction Направление сортировки, desc/asc.
     *
     * @return array
     */
    public function getUserNotificationsPage(int $user_id, int $per_page, int $cur_page, string $order_field, string $order_direction): array
    {
        $query = SystemUsersNotifications::where('user_id', $user_id)
            ->orderBy($order_field, $order_direction);

        $results = $query->paginate($per_page, ['*'], 'page', $cur_page);
        Config::set('total_records', $results->total());
        return [
            'results' => $results->items(),
            'total' => Config::get('total_records'),
            'current_page' => $results->currentPage(),
            'last_page' => $results->lastPage(),
        ];
    }

    public function notifications_send_user($user_data, $notification_message_data, $notification_data = false, $author_id = false)
    {
        if($notification_message_data === false)
        {
            var_export(1);
            return false;
        }
        if($user_data === false)
        {
            var_export(2);
            return false;
        }
        if($notification_message_data['message_status_front'] == 'enabled' && !empty($notification_message_data['message_text_front']))
        {
            var_export(3);
            $insert_data = [
                'user_id'							=> $user_data['user_id'],
                'notification_type'					=> $notification_message_data['notification_type']
            ];

            if($author_id !== false)
            {
                $insert_data['author_id']			= $author_id;
            }
var_export(4);
            $insert_data['notification_title']		= SystemFilter::replace_action($notification_message_data['message_title_front'], $notification_data);
            var_export(5);
            $insert_data['notification_text']		= SystemFilter::replace_action($notification_message_data['message_text_front'], $notification_data);
            //$this->userNotificationsCreate(new Request($insert_data));
            //$this->insert(DB_MAIN, SCHEMA_SYSTEM . '.' . TBL_USERS_NOTIFICATIONS, $insert_data);

        }
        var_export(6);
    }

    /**
     * Подготовить список чатов Telegram для отправки сообщений
     * @param $message_text
     *
     * @return void
     */
    public function notificationsSendAdmin($message_text): void
    {
        $admin_list = SystemSetting::query()
            ->where('owner_type', 'telegram_informer_users')
            ->where('setting_code', 'user_id')
            ->pluck('setting_value'); // Предполагается, что идентификаторы хранятся в поле 'setting_value'

        $environment = env('ENVIRONMENT');

        $admin_list->each(function ($user_id) use ($message_text, $environment) {
            $this->notificationsSendTelegram($user_id, "{$environment}: {$message_text}");
        });
    }

    /**
     * Отправить уведомление в чат Telegram
     * @param $user_id          - пользователь (чат ID)
     * @param $message_text     - сообщение
     *
     * @return void
     */
    private function notificationsSendTelegram($user_id, $message_text): void
    {
        try {
            Telegram::sendMessage([
                'chat_id' => $user_id,
                'text' => $message_text,
            ]);
        }
        catch (Exception $e){
            // TODO - возможно тут надо куда то вывести информацию о том, что сообщение в телегу не доставлено
        }
    }

    /**
     * Получить данные по уведомлению
     * @param int $notification_id - номер уведомления
     *
     * @return object|null
     */
    public function notificationData(int $notification_id): ?object
    {
        return SystemUsersNotifications::query()
            ->select('system_users_notifications.*', 'system_users.*')
            ->leftJoin('system_users', 'system_users.user_id', '=', 'system_users_notifications.user_id')
            ->where('system_users_notifications.notification_id', $notification_id)
            ->first();
    }

    /**
     * Получить уведомление по типу
     * @param $notification_type
     *
     * @return SystemUsersNotifications|null
     */
    public function getNotificationMessageType($notification_type): ?SystemUsersNotifications
    {
        return SystemUsersNotifications::query()
            ->where('notification_type', $notification_type)
            ->first();
    }

    /**
     * Получить правила валидации
     *
     * @param Request $request
     *
     * @return array
     */
    private function getValidationRules(Request $request): array
    {
        $systemUser = new SystemUsers();
        return [
            $this->primaryKey        =>
                $request->isMethod('put') ? 'required|exists:.' . $this->getTable() . ',' . $this->primaryKey
                    : 'numeric|min_digits:1|max_digits:9',
            'user_id'                => 'required|exists:.' . $systemUser->getTable() . ','
                . $systemUser->getPrimaryKey(),
            'author_id'              => 'nullable|exists:.' . $systemUser->getTable() . ','
                . $systemUser->getPrimaryKey(),
            'notification_type'      => [
                'required',
                Rule::enum(SystemNotificationsTypesEnum::class)
            ],
            'notification_title'     => 'required|string|max:55',
            'notification_text'      => 'required|string',
            'notification_date_add'  => 'required|date_format:"Y-m-d H:i:s"',
            'notification_date_view' => 'nullable|date_format:"Y-m-d H:i:s"',
        ];
    }

    /**
     * Получить количество уведомлений
     *
     * @param int $user_id
     *
     * @return array
     */
    public function getNotificationsCountByUserId(int $user_id): array
    {
        return ["count_new" => SystemUsersNotifications::where([
                ['user_id', '=', $user_id],
                ['notification_date_view', '=', null]
            ])->count() ?? 0,
                "count_old" => SystemUsersNotifications::where([
                        ['user_id', '=', $user_id],
                    ])->count() ?? 0
        ];
    }

    /**
     * Получить сообщения об ошибках валидации
     *
     * @return array
     */
    private function getValidationMessages(): array
    {
        return [
            $this->primaryKey        => trans('svr-core-lang::validation'),
            'user_id'                => trans('svr-core-lang::validation'),
            'author_id'              => trans('svr-core-lang::validation'),
            'notification_type'      => trans('svr-core-lang::validation'),
            'notification_title'     => trans('svr-core-lang::validation'),
            'notification_text'      => trans('svr-core-lang::validation'),
            'notification_date_add'  => trans('svr-core-lang::validation'),
            'notification_date_view' => trans('svr-core-lang::validation'),
        ];
    }
}
