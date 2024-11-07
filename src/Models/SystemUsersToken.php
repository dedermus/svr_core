<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusEnum;

/**
 * Модель UsersToken
 */
class SystemUsersToken extends Model
{
    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_users_tokens';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'token_id';

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
            'token_id',		                // Инкремент
            'user_id',		                // ID пользователя
            'participation_id',	            // Идентификатор типа привязки
            'token_value',		            // Значение токена
            'token_client_ip',	            // IP адрес пользователя
            'token_client_agent',	        // Агент пользователя
            'browser_name',		            // Имя браузера
            'browser_version',	            // Версия браузера
            'platform_name',		        // Имя платформы
            'platform_version',	            // Версия платформы
            'device_type',		            // Тип устройства
            'token_last_login',	            // Таймстамп последнего входа
            'token_last_action',	        // Таймстамп последнего действия
            'token_status',		            // Статус токена
            'created_at',                   // Дата создания записи
            'updated_at',                   // Дата редактирования записи'
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
     * @param $request
     *
     * @return void
     */
    public function settingCreate(Request $request): void
    {
        $this->authorize();

        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }

    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function settingUpdate(Request $request): void
    {
        $this->authorize();

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
     * Определить, уполномочен ли пользователь выполнить этот запрос.
     * @return bool
     */
    public function authorize(): bool
    { echo 2;
        return auth()->check();
    }

    /**
     * Валидация запроса
     * @param Request $request
     */
    private function validateRequest(Request $request)
    {
        $rules = $this->getValidationRules($request);
        $messages = $this->getValidationMessages();
        $request->validateWithBag('default', $rules, $messages);
    }

    /**
     * Получить правила валидации
     * @param Request $request
     * @return array
     */
    private function getValidationRules(Request $request): array
    {
        $id = $request->input($this->primaryKey);
        $systemUser = new SystemUsers();
        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.'.$this->getTable(), $this->primaryKey),
            ],
            'user_id' => 'required|exists:.' . $systemUser->getTable() . ',' . $systemUser->getPrimaryKey(),
            'participation_id' => 'nullable|min_digits:1|max_digits:10',
            'token_value' => 'required|string|max:72|unique:.' . $this->getTable() . ',token_value,' . ($id ?? 'null') . ',' . $this->primaryKey,
            'token_client_ip' => 'required|ip',
            'token_client_agent' => 'required|string|max:256',
            'browser_name' => 'nullable|string|max:32',
            'browser_version' => 'nullable|string|max:32',
            'platform_name' => 'nullable|string|max:32',
            'platform_version' => 'nullable|string|max:32',
            'device_type' => 'required|string|max:32',
            'token_last_login' => 'required|min_digits:1|max_digits:10',
            'token_last_action' => 'required|min_digits:1|max_digits:10',
            'token_status' => [
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
            $this->primaryKey => trans('svr-core-lang::validation'),
            'user_id' => trans('svr-core-lang::validation'),
            'participation_id' => trans('svr-core-lang::validation'),
            'token_value' => trans('svr-core-lang::validation'),
            'token_client_ip' => trans('svr-core-lang::validation'),
            'token_client_agent' => trans('svr-core-lang::validation'),
            'browser_name' => trans('svr-core-lang::validation'),
            'browser_version' => trans('svr-core-lang::validation'),
            'platform_name' => trans('svr-core-lang::validation'),
            'platform_version' => trans('svr-core-lang::validation'),
            'device_type' => trans('svr-core-lang::validation'),
            'token_last_login' => trans('svr-core-lang::validation'),
            'token_last_action' => trans('svr-core-lang::validation'),
            'token_status' => trans('svr-core-lang::validation'),
        ];
    }
}
