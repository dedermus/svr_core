<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusEnum;

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
     * @param Request $request
     *
     * @return void
     */
    public function moduleCreate(Request $request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
    }

    /**
     * Обновить запись
     * @param Request $request
     *
     * @return void
     */
    public function moduleUpdate(Request $request): void
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
     * Валидация входных данных
     * @param $request
     *
     * @return void
     */
    private function rules_old($request): void
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

        // module_name - Название модуля
        $request->validate(
            ['module_name' => 'required|string|min:2|max:64'],
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

    /**
     * Валидация запроса
     * @param Request $request
     */
    private function validateRequest(Application|Request $request)
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

        $uniqueRule = is_null($id)
            ? 'unique:.' . $this->getTable() . ',module_slug'
            : 'unique:.' . $this->getTable() . ',module_slug,' . $id . ',' . $this->primaryKey;

        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.'.$this->getTable(), $this->primaryKey),
            ],
            'module_name' => 'required|string|min:2|max:64',
            'module_description' => 'required|string|max:100',
            'module_class_name' => 'required|string|max:32',
            'module_slug' => 'required|string|max:32|' . $uniqueRule,
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
            'module_name' => trans('svr-core-lang::validation'),
            'module_description' => trans('svr-core-lang::validation'),
            'module_class_name' => trans('svr-core-lang::validation'),
            'module_slug' => trans('svr-core-lang::validation'),
        ];
    }
}
