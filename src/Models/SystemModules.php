<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель Modules
 */
class SystemModules extends Model
{
    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_modules';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'module_id';

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
            'module_name',                  // Название модуля
            'module_description',           // Описание модуля
            'module_class_name',            // Имя класса модуля
            'module_slug',                  // Слаг для модуля (уникальный идентификатор)
            'created_at',                   // Дата создания записи
            'updated_at',                   // Дата редактирования записи
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
    public function moduleCreate($request): void
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
    public function moduleUpdate($request): void
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
        // получаем поля со значениями
        $data = $request->all();

        // получаем значение первичного ключа
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;

        // id - Первичный ключ
        if (!is_null($id)) {
            $request->validate(
                [$this->primaryKey => 'required|exists:'.$this->getTable().','.$this->primaryKey],
                [$this->primaryKey => trans('svr-core-lang::validation.required')],
            );
        }

        // module_name - Название модуля
        $request->validate(
            ['module_name' => 'required|string|max:64'],
            ['module_name' => trans('svr-core-lang::validation')],
        );

        // module_description - Описание модуля
        $request->validate(
            ['module_description' => 'required|string|max:100'],
            ['module_description' => trans('svr-core-lang::validation')],
        );

        // module_class_name - Имя класса модуля
        $request->validate(
            ['module_class_name' => 'required|string|max:32'],
            ['module_class_name' => trans('svr-core-lang::validation')],
        );

        // module_slug - Слаг для модуля (уникальный идентификатор)
        $unique = is_null($id)
            ? '|unique:.'.$this->getTable().',module_slug,null,'.$this->primaryKey
            : '|unique:.'.$this->getTable().',module_slug,'.$id.','.$this->primaryKey;
        $request->validate(
            ['module_slug' => 'required|string|max:32|'.$unique],
            ['module_slug' => trans('svr-core-lang::validation')],
        );
    }
}
