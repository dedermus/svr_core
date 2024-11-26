<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Traits\GetTableName;

/**
 * Модель Modules
 */
class SystemModules extends Model
{
    use GetTableName;
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
     * Валидация запроса
     * @param Request $request
     *
     * @return void
     */
    private function validateRequest(Request $request): void
    {
        $rules = $this->getValidationRules($request);
        $messages = $this->getValidationMessages();
        $request->validate($rules, $messages);
    }

    /**
     * Получить правила валидации по переданному фильтру полей
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
                Rule::exists('.' . $this->getTable(), $this->primaryKey),
            ],
            'module_name' => 'required|string|min:2|max:64',
            'module_description' => 'required|string|max:100',
            'module_class_name' => 'required|string|max:32',
            'module_slug' => 'required|string|max:32|' . $uniqueRule,
        ];
    }

    /**
     * Получить сообщения об ошибках валидации по переданному фильтру полей
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
