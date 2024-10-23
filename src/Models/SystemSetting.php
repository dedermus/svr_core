<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель Setting
 */
class SystemSetting extends Model
{
    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_settings';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'setting_id';

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
            'owner_type',                   // признак принадлежности записи
            'owner_id',                     // идентификатор принадлежности записи
            'setting_code',                 // код записи
            'setting_value',                // значение
            'setting_value_alt',            // альтернативное значение
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

        // owner_type - признак принадлежности записи
        $request->validate(
            ['owner_type' => 'required|string|max:255'],
            ['owner_type' => trans('svr-core-lang::validation')],
        );

        // owner_id - идентификатор принадлежности записи
        $request->validate(
            ['owner_id' => 'required|numeric|max:99999999999'],
            ['owner_id' => trans('svr-core-lang::validation')],
        );

        // setting_code - код записи
        $request->validate(
            ['setting_code' => 'required|string|max:50'],
            ['setting_code' => trans('svr-core-lang::validation')],
        );

        // setting_value - значение
        $request->validate(
            ['setting_value' => 'required|string'],
            ['setting_value' => trans('svr-core-lang::validation')],
        );

        // setting_value_alt - альтернативное значение
        $request->validate(
            ['setting_value_alt' => 'nullable|string|max:255'],
            ['setting_value_alt' => trans('svr-core-lang::validation')],
        );
    }
}
