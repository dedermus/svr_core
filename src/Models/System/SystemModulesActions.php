<?php

namespace Svr\Core\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель ModulesActions
 */
class SystemModulesActions extends Model
{
    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     * @var string
     */
    protected $table								= 'system.system_modules_actions';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    protected $primaryKey							= 'right_id';

    /**
     * Поле даты создания строки
     * @var string
     */
    const CREATED_AT								= 'created_at';

    /**
     * Поле даты обновления строки
     * @var string
     */
    const UPDATED_AT								= 'updated_at';

    /**
     * Поля, которые можно менять сразу массивом
     * @var array
     */
    protected $fillable								= [
        'module_slug',								// MODULE_SLUG в таблице SYSTEM.SYSTEM_MODULES
        'right_action',								// Экшен
        'right_name',								// Имя экшена
        'right_slug',								// Слаг для экшена (уникальный составной идентификатор из module_slag + right_slug)
        'right_content_type',						// Тип запроса
        'right_log_write',							// Флаг записи данных в таблицу логов
        'created_at',							    // Дата и время создания
        'updated_at',								// Дата и время модификации
    ];

    protected $dates = [
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
        // получаем id
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
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
                [$this->primaryKey => 'required|exists:.'.$this->getTable().','.$this->primaryKey],
                [$this->primaryKey => trans('svr-core-lang::validation.required')],
            );
        }

        // MODULE_SLUG в таблице SYSTEM.SYSTEM_MODULES
        $request->validate(
            ['module_slug' => 'required|string|min:3|max:64'],
            ['module_slug' => trans('svr-core-lang::validation')],
        );

        // right_action - Экшен
        $request->validate(
            ['right_action' => 'required|string|min:3|max:32'],
            ['right_action' => trans('svr-core-lang::validation')],
        );

        // right_name - Имя экшена
        $request->validate(
            ['right_name' => 'required|string|min:3|max:32'],
            ['right_name' => trans('svr-core-lang::validation')],
        );

        // right_slug - Слаг для экшена (уникальный составной идентификатор из module_slag + right_slug)
        $unique = is_null($id)
            ? '|unique:.'.$this->getTable().',right_slug,null,'.$this->primaryKey
            : '|unique:.'.$this->getTable().',right_slug,'.$id.','.$this->primaryKey;

        $request->validate(
            ['right_slug' => 'required|string|max:65|'.$unique],
            ['right_slug' => trans('svr-core-lang::validation')],
        );

        // right_content_type - Тип запроса
        $request->validate(
            ['right_content_type' => 'required|string|min:3|max:32'],
            ['right_content_type' => trans('svr-core-lang::validation')],
        );

        // right_log_write - Флаг записи данных в таблицу логов
        $request->validate(
            ['right_log_write' => 'required'],
            ['right_log_write' => trans('svr-core-lang::validation')],
        );
    }
}
