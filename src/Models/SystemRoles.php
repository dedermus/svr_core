<?php

namespace Svr\Core\Models;

use Svr\Core\Traits\GetEnums;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Модель Roles
 */
class SystemRoles extends Model
{
    use GetEnums;

    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     * @var string
     */
    protected $table = 'system.system_roles';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    protected $primaryKey = 'role_id';

    /**
     * Поле даты создания строки
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * Поле даты обновления строки
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Поля, которые можно менять сразу массивом
     * @var array
     */
    protected $fillable = [
        'role_name_long',
        'role_name_short',
        'role_slug',
        'role_status',
        'role_status_delete',
        'created_at',
        'updated_at',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
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
     * A role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return  $this->belongsToMany(
            SystemRolesRights::class,
            'system.system_roles',
            'role_slug',
            'role_slug',
            'role_slug',
            'role_slug');
    }

    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function roleCreate($request): void
    {
        $this->rules($request);
        $this->fill($request->all());
        $this->save();
        $role_data = $this->find($this->getKey()); // получим данные по новой записи
        $data = $request->all();

        if(isset($data['role_rights_list']) && $role_data)
        {
            SystemRolesRights::roleRightsStore($role_data, $data['role_rights_list']);
        }
    }

    /**
     * Обновить запись
     *
     * @param $request
     *
     * @return void
     */
    public function roleUpdate($request): void
    {
        // валидация
        self::rules($request);
        // получаем массив полей и значений и з формы
        $data = $request->all();
        // получаем id
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
        // готовим сущность для обновления
        $modules_data = $this->find($id);
        // обновляем запись
        $modules_data->update($data);
        $role_data = $this->findOrFail($id);
        // обновим связь прав и ролей
        SystemRolesRights::roleRightsStore($role_data, $data['role_rights_list']);
    }

    /**
     * Валидация входных данных
     * @param $request
     *
     * @return void
     */
    private function rules($request): void
    {
        // получаем поля со значениями
        $data = $request->all();

        // получаем значение первичного ключа
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;

        // id - Первичный ключ
        if (!is_null($id)) {
            $request->validate(
                [$this->primaryKey => 'required|exists:.'.$this->getTable().','.$this->primaryKey],
                [$this->primaryKey => trans('svr-core-lang::validation.required')],
            );
        }

        // role_name_long - Длинное название
        $request->validate(
            ['role_name_long' => 'required|string|min:3|max:64'],
            ['role_name_long' => trans('svr-core-lang::validation')],
        );

        // role_name_short - Короткое название роли
        $request->validate(
            ['role_name_long' => 'required|string|min:3|max:32'],
            ['role_name_long' => trans('svr-core-lang::validation')],
        );

        // role_slug - Слаг для роли (уникальный идентификатор)
        $unique = is_null($id)
            ? '|unique:.'.$this->getTable().',role_slug,null,'.$this->primaryKey
            : '|unique:.'.$this->getTable().',role_slug,'.$id.','.$this->primaryKey;

        $request->validate(
            ['role_slug' => 'required|string|max:32|'.$unique],
            ['role_slug' => trans('svr-core-lang::validation')],
        );

        // role_status - Статус роли
        $request->validate(
            ['role_status' => 'required|string|min:3|max:32'],
            ['role_status' => trans('svr-core-lang::validation')],
        );

        // role_status_delete - Флаг удаление роли
        $request->validate(
            ['role_status_delete' => 'required'],
            ['role_status_delete' => trans('svr-core-lang::validation')],
        );
    }
}
