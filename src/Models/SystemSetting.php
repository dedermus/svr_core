<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Traits\GetTableName;
use Svr\Core\Traits\GetValidationRules;

/**
 * Модель Setting
 */
class SystemSetting extends Model
{
    use GetTableName;
    use HasFactory;
    use GetValidationRules;

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
     * @param Request $request
     *
     * @return void
     */
    public function settingCreate(Request $request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }

    /**
     * Обновить запись
     *
     * @param Request $request
     *
     * @return void
     */
    public function settingUpdate(Request $request): void
    {
        $this->validateRequest($request);
        $data = $request->all();
        $id = $data[$this->primaryKey] ?? null;

        if ($id) {
            $setting = $this->find($id);
            if ($setting) {
                $setting->update($data);
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

        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.' . $this->getTable(), $this->primaryKey),
            ],
            'owner_type' => 'required|string|max:255',
            'owner_id' => 'required|numeric|max:99999999999',
            'setting_code' => 'required|string|max:50',
            'setting_value' => 'required|string',
            'setting_value_alt' => 'nullable|string|max:255',
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
            'owner_type' => trans('svr-core-lang::validation'),
            'owner_id' => trans('svr-core-lang::validation'),
            'setting_code' => trans('svr-core-lang::validation'),
            'setting_value' => trans('svr-core-lang::validation'),
            'setting_value_alt' => trans('svr-core-lang::validation'),
        ];
    }
}
