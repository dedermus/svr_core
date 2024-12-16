<?php

namespace Svr\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use OpenAdminCore\Admin\LogViewer\LogViewer;
use OpenAdminCore\Admin\Reporter\ExceptionModel;
use PHPMailer\PHPMailer\PHPMailer;
use Svr\Core\Enums\SystemNotificationsTypesEnum;
use Svr\Core\Extensions\Email\SystemEmail;
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
        return  $results->items();
    }

    /**
     * Создать уведомление для пользователя
     * @param string      $notification_type
     * @param false|int   $company_id
     * @param false|int   $user_id
     * @param false|array $notification_data
     *
     * @return void
     */
    public function notificationCreate(string $notification_type, false|int $company_id = false, false|int $user_id = false, false|array $notification_data = false): void
    {
        $notification_message_data = (new SystemUsersNotificationsMessages())->getNotificationMessageData($notification_type);

        if($company_id)
        {
            $company_users_list		= (new SystemUsers())->users_list(999999, 1, true, ['company_id' => [$company_id]]);

            if(count($company_users_list) > 0)
            {
                foreach($company_users_list as $item)
                {
                    $this->notification_send_user($item, $notification_message_data, $notification_data);
                }
            }
        }
        if($user_id)
        {
            $user_data	= SystemUsers::getUser($user_id);

            $this->notification_send_user($user_data, $notification_message_data, $notification_data);
        }
    }

    /**
     * Создание уведомления для пользователя
     *
     * @param $user_data                 - массив данных по пользователю
     * @param $notification_message_data - данные шаблона уведомления
     * @param array|false $notification_data   - данные для подстановки в шаблон
     * @param int|false $author_id           - автор уведомления
     *
     * @return false|void
     */
    public function notification_send_user($user_data, $notification_message_data, array|false $notification_data = false, int|false $author_id = false)
    {
        if($notification_message_data === false)
        {
            return false;
        }
        if($user_data === false)
        {
            return false;
        }

        if($notification_message_data['message_status_front'] == 'enabled' && !empty($notification_message_data['message_text_front']))
        {
            $insert_data = [
                'user_id'							=> $user_data['user_id'],
                'notification_type'					=> $notification_message_data['notification_type']
            ];

            if($author_id !== false)
            {
                $insert_data['author_id']			= $author_id;
            }
            $insert_data['notification_title']		= SystemFilter::replace_action($notification_message_data['message_title_front'], $notification_data);
            $insert_data['notification_text']		= SystemFilter::replace_action($notification_message_data['message_text_front'], $notification_data);
            $insert_data['notification_date_add'] = now()->format($this->dateFormat);
            $this->userNotificationsCreate(new Request($insert_data));
        }

        if($notification_message_data['message_status_email'] == 'enabled' && !empty($notification_message_data['message_text_email']))
        {
            if(empty($user_data['user_email']) || $user_data['user_email_status'] !== 'confirmed')
            {
                return false;
            }
            if (is_null($notification_message_data['message_title_email']) || is_null($notification_message_data['message_text_email']))
            {
                return false;
            }

            // TODO - подставить электронный адрес пользователя
            $email = 'dedermus@gmail.com';//$user_data['user_email'];
            $title = SystemFilter::replace_action($notification_message_data['message_title_email'], $notification_data);
            $message = SystemFilter::replace_action($notification_message_data['message_text_email'], $notification_data);
            SystemEmail::sendEmailCustom($email, $title, $message);
        }
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
    public function getNotificationData(int $notification_id): ?object
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
    public function getNotificationTypeData($notification_type): ?SystemUsersNotifications
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
