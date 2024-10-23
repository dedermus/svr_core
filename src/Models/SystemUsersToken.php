<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    public function settingCreate($request): void
    {
        $this->rules($request);
        $this->fill($request->all());
        $this->save();
    }

    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function settingUpdate($request): void
    {
        // валидация
        $this->rules($request);
        // получаем массив полей и значений и з формы
        $data = $request->all();
        if (!isset($data[$this->primaryKey])) return;
        // получаем id
        $id = $data[$this->primaryKey];
        // готовим сущность для обновления
        $modules_data = $this->find($id);
        // обновляем запись
        $modules_data->update($data);
    }

    /**
     * Валидация входных данных
     * @param $request
     *
     * @return void
     */
    private function rules($request): void
    {
        // модель
        $systemUser = new SystemUsers();

        // получаем поля со значениями
        $data = $request->all();

        // лист ENUM
        $enum_list = implode(',', SystemStatusEnum::get_option_list());

        // получаем значение первичного ключа
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;

        // id - Первичный ключ
        if (!is_null($id)) {
            $request->validate(
                [$this->primaryKey => 'required|exists:.'.$this->getTable().','.$this->primaryKey],
                [$this->primaryKey => trans('svr-core-lang::validation')],
            );
        }

        // user_id - Идентификатор пользователя
        $request->validate(
            ['user_id' => 'required|exists:.'.$systemUser->getTable().','.$systemUser->getPrimaryKey()],
            ['user_id' => trans('svr-core-lang::validation')],
        );

        // participation_id - Идентификатор типа привязки
        $request->validate(
            ['participation_id' => 'nullable|min_digits:1|max_digits:10'],
            ['participation_id' => trans('svr-core-lang::validation')],
        );

        // token_value - Значение токена (уникальный идентификатор)
        $unique = is_null($id)
            ? '|unique:.'.$this->getTable().',token_value,null,'.$this->primaryKey
            : '|unique:.'.$this->getTable().',token_value,'.$id.','.$this->primaryKey;
        $request->validate(
            ['token_value' => 'required|string|max:72'.$unique],
            ['token_value' => trans('svr-core-lang::validation')],
        );

        // token_client_ip - IP адрес пользователя
        $request->validate(
            ['token_client_ip' => 'required|ip'],
            ['token_client_ip' => trans('svr-core-lang::validation')],
        );

        // token_client_agent - Агент пользователя
        $request->validate(
            ['token_client_agent' => 'required|string|max:256'],
            ['token_client_agent' => trans('svr-core-lang::validation')],
        );

        // browser_name - Название браузера
        $request->validate(
            ['browser_name' => 'nullable|string|max:32'],
            ['browser_name' => trans('svr-core-lang::validation')],
        );

        // browser_version - Версия браузера
        $request->validate(
            ['browser_version' => 'nullable|string|max:32'],
            ['browser_version' => trans('svr-core-lang::validation')],
        );

        // platform_name - Имя платформы
        $request->validate(
            ['platform_name' => 'nullable|string|max:32'],
            ['platform_name' => trans('svr-core-lang::validation')],
        );

        // platform_version - Версия платформы
        $request->validate(
            ['platform_version' => 'nullable|string|max:32'],
            ['platform_version' => trans('svr-core-lang::validation')],
        );

        // device_type - Тип устроиства
        $request->validate(
            ['device_type' => 'required|string|max:32'],
            ['device_type' => trans('svr-core-lang::validation')],
        );

        // token_last_login - Таймстамп последнего входа
        $request->validate(
            ['token_last_login' => 'required|min_digits:1|max_digits:10'],
            ['token_last_login' => trans('svr-core-lang::validation')],
        );

        // token_last_action - Таймстамп последнего действия
        $request->validate(
            ['token_last_action' => 'required|min_digits:1|max_digits:10'],
            ['token_last_action' => trans('svr-core-lang::validation')],
        );

        // token_status - Статус токена
        $request->validate(
            ['token_status' => 'required|in:'.$enum_list],
            ['token_status' => trans('svr-core-lang::validation')],
        );
    }
}
