<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemNotificationsTypesEnum;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;

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
     * Получить список уведомлений по USER_ID  с пагинацией
     * @param $user_id         - пользователь USER_ID
     * @param $per_page        - текущая страница
     * @param $cur_page        - максимальное количество записей на странице
     * @param $order_field     - поле сортировки, строка, 50 символов
     * @param $order_direction - направление сортировки, desc/asc
     *
     * @return array
     */
    public function notificationListUserIdPage($user_id, $per_page, $cur_page, $order_field, $order_direction): array
    {
//        dd(SystemUsersNotifications::where('user_id', $user_id)
//            ->orderBy($order_field, $order_direction)
//            ->take($per_page)
//            ->skip($cur_page)->toSql());
        return [
//            'results' => SystemUsersNotifications::where('user_id', $user_id)
//                ->orderBy($order_field, $order_direction)
//                ->limit($cur_page-1)
//                ->paginate($per_page),
            'results' => SystemUsersNotifications::where('user_id', $user_id)
                ->orderBy($order_field, $order_direction)
                ->take($per_page) // limit
                ->skip(($cur_page-1)*$per_page) // offset
                ->get(),

            'total' =>SystemUsersNotifications::where('user_id', $user_id)
        ->count()
        ];
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
            $this->primaryKey =>
                $request->isMethod('put') ? 'required|exists:.'.$this->getTable().','.$this->primaryKey : 'numeric|min_digits:1|max_digits:9',
            'user_id' => 'required|exists:.' . $systemUser->getTable() . ','
                . $systemUser->getPrimaryKey(),
            'author_id' => 'nullable|exists:.' . $systemUser->getTable() . ','
                . $systemUser->getPrimaryKey(),
            'notification_type' => [
                'required',
                Rule::enum(SystemNotificationsTypesEnum::class)
            ],
            'notification_title' => 'required|string|max:55',
            'notification_text' => 'required|string',
            'notification_date_add' => 'required|date_format:"Y-m-d H:i:s"',
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
        return ["count_new" => $this::where([
                ['user_id', '=', $user_id],
                ['notification_date_view', '=', null]
            ])->count() ?? 0,
            "count_old" => $this::where([
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
            $this->primaryKey => trans('svr-core-lang::validation'),
            'user_id' => trans('svr-core-lang::validation'),
            'author_id' => trans('svr-core-lang::validation'),
            'notification_type' => trans('svr-core-lang::validation'),
            'notification_title' => trans('svr-core-lang::validation'),
            'notification_text' => trans('svr-core-lang::validation'),
            'notification_date_add' => trans('svr-core-lang::validation'),
            'notification_date_view' => trans('svr-core-lang::validation'),
        ];
    }
}
