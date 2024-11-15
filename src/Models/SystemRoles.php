<?php

namespace Svr\Core\Models;

use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Illuminate\Validation\Rule;
use Svr\Core\Traits\GetEnums;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

/**
 * Модель Roles
 */
class SystemRoles extends Model
{
    use GetEnums, HasFactory;

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

    /**
     * Поля дат
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
     * A role belongs to many permissions.
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            SystemRolesRights::class,
            'system.system_roles',
            'role_slug',
            'role_slug',
            'role_slug',
            'role_slug'
        );
    }

    /**
     * Создать запись
     *
     * @param Request $request
     *
     * @return void
     */
    public function roleCreate(Request $request): void
    {
        $this->validateRequest($request);
        $this->fill($request->all())->save();
        $role_data = $this->find($this->getKey());
        SystemRolesRights::roleRightsStore($role_data, $request);
    }

    /**
     * Обновить запись
     * @param Request $request
     * @return void
     */
    public function roleUpdate(Request $request): void
    {
        $this->validateRequest($request);
        $data = $request->all();
        $id = $data[$this->primaryKey] ?? null;

        if ($id) {
            $module = $this->find($id);
            if ($module) {
                $module->update($data);
                $role_data = $this->find($data[$this->primaryKey]);
                SystemRolesRights::roleRightsStore($role_data, $request);
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
            'role_name_long' => 'required|string|min:3|max:64',
            'role_name_short' => 'required|string|min:3|max:32',
            'role_slug' => [
                'required',
                'string',
                'max:32',
                Rule::unique('.'.$this->getTable())->ignore($id, $this->primaryKey)
            ],
            'role_status' => [
                'required',
                'string',
                'min:3',
                'max:32',
                Rule::enum(SystemStatusEnum::class)
            ],
            'role_status_delete' => [
                'required',
                Rule::enum(SystemStatusDeleteEnum::class)
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
            'role_name_long' => trans('svr-core-lang::validation'),
            'role_name_short' => trans('svr-core-lang::validation'),
            'role_slug' => trans('svr-core-lang::validation'),
            'role_status' => trans('svr-core-lang::validation'),
            'role_status_delete' => trans('svr-core-lang::validation'),
        ];
    }
}
