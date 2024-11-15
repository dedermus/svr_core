<?php
namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemStatusEnum;

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
    protected $table = 'system.system_modules_actions';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    protected $primaryKey = 'right_id';

    /**
     * Поля, которые можно менять сразу массивом
     * @var array
     */
    protected $fillable = [
        'module_slug',
        'right_action',
        'right_name',
        'right_slug',
        'right_content_type',
        'right_log_write',
        'created_at',
        'updated_at',
    ];

    /**
     * Поля, которые должны быть преобразованы в даты
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Формат хранения столбцов даты модели.
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Включение автоматического управления временными метками
     * @var bool
     */
    public $timestamps = true;

    /**
     * Создать запись
     * @param Request $request
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
     * @param Request $request      - Запрос
     * @param         $filterKeys   - Список необходимых полей
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

        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.'.$this->getTable(), $this->primaryKey),
            ],
            'module_slug' => 'required|string|min:3|max:64',
            'right_action' => 'required|string|min:3|max:32',
            'right_name' => 'required|string|min:3|max:32',
            'right_slug' => [
                'required',
                'string',
                'min:3',
                'max:65',
                Rule::unique('.'.$this->getTable(), 'right_slug')->ignore($id, $this->primaryKey),
            ],
            'right_content_type' => 'required|string|min:3|max:32',
            'right_log_write' => [
                'required',
                Rule::enum(SystemStatusEnum::class),
            ],
        ];
    }

    /**
     * Получить сообщения об ошибках валидации по переданному фильтру полей
     * @param $filterKeys   - Список необходимых полей
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
            'module_slug' => trans('svr-core-lang::validation'),
            'right_action' => trans('svr-core-lang::validation'),
            'right_name' => trans('svr-core-lang::validation'),
            'right_slug' => trans('svr-core-lang::validation'),
            'right_content_type' => trans('svr-core-lang::validation'),
            'right_log_write' => trans('svr-core-lang::validation'),
        ];
    }
}
