<?php

namespace Svr\Core\Models;

use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Traits\GetTableName;

/**
 * Модель UsersToken
 */
class SystemUsersToken extends Model
{
    use GetTableName;
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
            'token_id',                        // Инкремент
            'user_id',                        // ID пользователя
            'participation_id',                // Идентификатор типа привязки
            'token_value',                    // Значение токена
            'token_client_ip',                // IP адрес пользователя
            'token_client_agent',            // Агент пользователя
            'browser_name',                    // Имя браузера
            'browser_version',                // Версия браузера
            'platform_name',                // Имя платформы
            'platform_version',                // Версия платформы
            'device_type',                    // Тип устройства
            'token_last_login',                // Таймстамп последнего входа
            'token_last_action',            // Таймстамп последнего действия
            'token_status',                    // Статус токена
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
     * @param Request $request
     *
     * @return void
     */
    public function userTokenCreate($data): void
    {
        $model = new SystemUsersToken();
        $filterKeys = $this->fillable;
        $rules = $model->getFilterValidationRules($data, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        Validator::make(
            is_array($data) ? $data : $data->toArray(),
            $rules,
            $messages
        )->validate();
        $this->fill(is_array($data) ? $data : $data->all())->save();
    }

    /**
     * Добавить в БД запись токена
     * @param $data
     * @return mixed
     */
    public function userTokenStore($data): mixed
    {
        return SystemUsersToken::create([
            'user_id' => $data['user_id'],
            'participation_id' => $data['participation_id'],
            'token_value' => $data['token_value'],
            'token_client_ip' => $data['token_client_ip'],
            'token_client_agent' => Browser::userAgent(),
            'browser_name' => Browser::browserFamily(),
            'browser_version' => Browser::browserVersion(),
            'platform_name' => Browser::platformFamily(),
            'platform_version' => Browser::platformVersion(),
            'device_type' => strtolower(Browser::deviceType()),
            'token_last_login' => getdate()[0],
            'token_last_action' => getdate()[0],
            'token_status' => SystemStatusEnum::ENABLED->value
        ]);
    }

    /**
     * Обновить запись
     * @param Request $request
     *
     * @return void
     */
    public function userTokenUpdate(Request $request): void
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
        $id = $request->input($this->primaryKey);
        $systemUser = new SystemUsers();
        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.' . $this->getTable(), $this->primaryKey),
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

    /**
     * Получить крайний токен
     * @param $user_id - пользователь
     *
     * @return mixed
     */
    public static function userLastTokenData($user_id)
    {
        return SystemUsersToken::where('user_id', '=', $user_id)
            ->whereNotNull('participation_id')
            ->latest('updated_at')->first();
    }
}
