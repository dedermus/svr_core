<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemNotificationsTypesEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;

/**
 * Модель Setting
 */
class SystemUsersNotificationsMessages extends Model
{
    use GetTableName;
    use HasFactory;
    use GetValidationRules;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_users_notifications_messages';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'message_id';

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
            'notification_type',    //Тип уведомления
            'message_description',  //Системное описание
            'message_title_front',  //Заголовок сообщения для отправки на фронт
            'message_title_email',  //Заголовок сообщения электронного письма
            'message_text_front',   //Текст сообщения для отправки на фронт
            'message_text_email',   //Текст сообщения электронного письма
            'message_status_front', //Флаг работы с фронтом
            'message_status_email', //Флаг работы с электронной почтой
            'message_status',       //Статус уведомления
            'created_at',           //Дата создания
            'updated_at',           //Дата обновления
        ];

    /**
     * @var array|string[]
     */
    protected array $dates
        = [
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
    public function notificationsCreate(Request $request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }

    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function notificationsUpdate(Request $request): void
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
     * Получить правила валидации
     * @param Request $request
     * @return array
     */
    private function getValidationRules(Request $request): array
    {
        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.' . $this->getTable(), $this->primaryKey),
            ],
            'notification_type' => [
                'required',
                Rule::enum(SystemNotificationsTypesEnum::class)
            ],
            'message_description' => 'required|string|max:255',
            'message_title_front' => 'required|string|max:55',
            'message_title_email' => 'nullable|string|max:55',
            'message_text_front' => 'required|string',
            'message_text_email' => 'nullable|string',
            'message_status_front' => [
                'required',
                Rule::enum(SystemStatusEnum::class)
            ],
            'message_status_email' => [
                'required',
                Rule::enum(SystemStatusEnum::class)
            ],
            'message_status' => [
                'required',
                Rule::enum(SystemStatusEnum::class)
            ],
        ];
    }

    /**
     * Получить сообщения об ошибках валидации
     * @return array
     */
    private function getValidationMessages(): array
    {
        return [
            $this->primaryKey => trans('svr-core-lang::validation.required'),
            'notification_type' => trans('svr-core-lang::validation'),
            'message_description' => trans('svr-core-lang::validation'),
            'message_title_front' => trans('svr-core-lang::validation'),
            'message_title_email' => trans('svr-core-lang::validation'),
            'message_text_front' => trans('svr-core-lang::validation'),
            'message_text_email' => trans('svr-core-lang::validation'),
            'message_status_front' => trans('svr-core-lang::validation'),
            'message_status_email' => trans('svr-core-lang::validation'),
            'message_status' => trans('svr-core-lang::validation'),
        ];
    }
}
