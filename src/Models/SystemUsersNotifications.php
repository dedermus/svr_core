<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemNotificationsTypesEnum;

/**
 * Модель Setting
 */
class SystemUsersNotifications extends Model
{
    use HasFactory;

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
     * Валидация запроса
     *
     * @param Request $request
     */
    private function validateRequest(Request $request): void
    {
        $rules = $this->getValidationRules($request);
        $messages = $this->getValidationMessages();
        $request->validate($rules, $messages);
    }

    /**
     * Получить правила валидации по переданному фильтру полей
     *
     * @param Request $request    - Запрос
     * @param         $filterKeys - Список необходимых полей
     *
     * @return array
     */
    public function getFilterValidationRules(Request $request, $filterKeys): array
    {
        return array_intersect_key($this->getValidationRules($request), array_flip($filterKeys));
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
            $this->primaryKey        => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.' . $this->getTable(), $this->primaryKey),
            ],
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
            'notification_date_add'  => 'required|data',
            'notification_date_view' => 'nullable|data',
        ];
    }

    /**
     * Получить сообщения об ошибках валидации по переданному фильтру полей
     *
     * @param $filterKeys - Список необходимых полей
     *
     * @return array
     */
    public function getFilterValidationMessages($filterKeys): array
    {
        return array_intersect_key($this->getValidationMessages(), array_flip($filterKeys));
    }

    /**
     * Получить сообщения об ошибках валидации
     *
     * @return array
     */
    private function getValidationMessages(): array
    {
        return [
            $this->primaryKey        => trans('svr-core-lang::validation.required'),
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
