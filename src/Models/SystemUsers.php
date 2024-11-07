<?php

namespace Svr\Core\Models;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemSexEnum;
use Svr\Core\Enums\SystemStatusConfirmEnum;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Enums\SystemStatusNotificationEnum;
use Svr\Core\Traits\GetEnums;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class SystemUsers extends Model
{
    use GetEnums;

    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     * @var string
     */
    protected $table								= 'system.system_users';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    protected $primaryKey							= 'user_id';

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
        'user_base_index',							// Базовый индекс хозяйства при автоматическом создании пользователей
        'user_guid',								// UUID4
        'user_first',								// Имя
        'user_middle',								// Отчество
        'user_last',								// Фамилия
        'user_avatar',								// Иконка (аватар)
        'user_password',							// Пароль
        'user_sex',									// Пол (гендерная принадлежность)
        'user_email',								// Электронный адрес
        'user_herriot_login',						// Статус электронного адреса
        'user_herriot_password',					// Логин в API herriot
        'user_herriot_web_login',					// Пароль в API herriot
        'user_herriot_web_password',				// Логин в WEB herriot
        'user_herriot_apikey',						// Пароль в WEB herriot
        'user_herriot_issuerid',					// apikey в herriot
        'user_herriot_serviceid',					// issuerid в herriot
        'user_email_status',						// serviceid в herriot
        'user_phone',								// Телефон
        'user_phone_status',						// Статус телефона
        'user_notifications',						// Подтверждение
        'user_status',								// Статус записи (активна/не активна)
        'user_status_delete',						// Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)
        'created_at',							    // Дата и время создания
        'updated_at',								// Дата и время модификации
        'user_date_created',						// Дата создания
        'user_date_update',							// Дата обновления
        'user_date_block',							// Дата блокировки
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'user_date_created',
        'user_date_update',
        'user_date_block',
    ];

    /**
     * Формат хранения столбцов даты модели.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Путь до папки с аватарами пользователя
     * @var string
     */
    protected string $pathAvatar = 'images/avatars/';

    /**
     * Диск хранения
     * @var string
     */
    protected string $diskAvatar = 'public/';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * Получить первичный ключ
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * Получить путь до папки с аватарами пользователя
     * @return string
     */
    public function getPathAvatar(): string
    {
        return $this->pathAvatar;
    }

    /**
     * Получить диск хранения аватарки
     * @return string
     */
    public function getDiskAvatar(): string
    {
        return $this->diskAvatar;
    }

    /**
     * Получить путь к аватару.
     * @param $avatar
     * @return string
     */
    public function getUrlAvatar($avatar): string
    {
        if (Storage::exists($this->getDiskAvatar() . $this->getPathAvatar() . $avatar) && !is_null($avatar)) {
            return asset($this->getPathAvatar() . $avatar);
        }

        $default = config('admin.default_avatar') ?: '/vendor/open-admin/open-admin/gfx/user.svg';
        return admin_asset($default);
    }

    /**
     * Перехват удаления записи
     * @return bool|null
     */
    public function delete()
    {
        $this->mergeAttributesFromCachedCasts();
        $data = $this->attributes;
        $id = $data[$this->primaryKey] ?? null;
        $res = $id ? SystemUsers::findOrFail($id)->toArray() : [];
        $avatar = $res['user_avatar'] ?? null;

        if (Storage::exists($this->getDiskAvatar() . $this->getPathAvatar() . $avatar) && !is_null($avatar)) {
            Storage::delete($this->getDiskAvatar() . $this->getPathAvatar() . $avatar);
        }

        return parent::delete();
    }

    /**
     * Добавление файла аватара на диск
     * @param $request
     * @return string|null
     */
    public function addFileAvatar($request): ?string
    {
        $filenamebild = null;

        if ($request->hasFile('user_avatar')) {
            $this->deleteAvatar($request);
            $filenameWithExt = $request->file('user_avatar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extention = $request->file('user_avatar')->getClientOriginalExtension();
            $filenamebild = $filename . "_" . time() . "." . $extention;
            $fileNameToStore = $this->getPathAvatar() . $filenamebild;
            $request->file('user_avatar')->storeAs($this->getDiskAvatar(), $fileNameToStore);
        } else {
            $data = $request->all();
            $id = $data[$this->primaryKey] ?? null;
            if ($id) {
                $res = SystemUsers::findOrFail($id)->toArray();
                $filenamebild = $res['user_avatar'] ?? $filenamebild;
            }
        }

        return $filenamebild;
    }

    /**
     * Удалить аватар с диска
     * @param $request
     *
     * @return bool
     */
    public function deleteAvatar($request): bool
    {
        $data = $request->all();
        $id = $data[$this->primaryKey] ?? null;
        $res = $id ? SystemUsers::findOrFail($id)->toArray() : [];
        $avatar = $res['user_avatar'] ?? null;
        // если файл аватар существует
        if (Storage::exists($this->getDiskAvatar() . $this->getPathAvatar() .$avatar) && !is_null($avatar)) {
            Storage::delete($this->getDiskAvatar() . $this->getPathAvatar() .$avatar);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Пользователь принадлежит ко многим ролям.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            SystemRoles::class,
            'system.system_users_roles',
            'user_id',
            'role_slug',
            'user_id',
            'role_slug');
    }

    /**
     * Создать запись
     *
     * @param Request $request
     *
     * @return void
     */
    public function userCreate(Request $request): void
    {
        $this->validateRequest($request);
        $data = $request->all();
        $data['user_avatar'] = $this->addFileAvatar($request);
        $this->fill($data)->save();
        $user_data = $this->find($this->getKey());

        if (isset($data['user_roles_list']) && $user_data) {
            SystemUsersRoles::userRolesStore($user_data, $data['user_roles_list']);
        }
    }

    /**
     * Обновить запись
     *
     * @param Request $request
     *
     * @return void
     */
    public function userUpdate(Request $request): void
    {
        $this->validateRequest($request);
        $data = $request->all();
        $data['user_avatar'] = $this->addFileAvatar($request);
        $id = $data[$this->primaryKey] ?? null;

        if ($id) {
            $modules_data = $this->find($id);
            $modules_data->update($data);
            $user_data = $this->findOrFail($id);
            SystemUsersRoles::userRolesStore($user_data, $data['user_roles_list']);
        }
    }

    /**
     * Валидация запроса
     * @param Request $request
     */
    private function validateRequest(Request $request)
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

        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.'.$this->getTable(), $this->primaryKey),
            ],
            'user_base_index' => 'nullable|string|size:7',
            'user_first' => 'nullable|string|min:1|max:32',
            'user_middle' => 'nullable|string|min:1|max:32',
            'user_last' => 'nullable|string|min:1|max:32',
            'user_avatar' => 'image|nullable|mimes:jpeg,jpg,png,gif|max:10000',
            'user_password' => 'required|min:1|max:64',
            'user_sex' => [
                'required',
                Rule::enum(SystemSexEnum::class)
            ],
            'user_herriot_login' => 'nullable|string|max:64',
            'user_herriot_password' => 'nullable|string|max:64',
            'user_herriot_web_login' => 'nullable|string|max:64',
            'user_herriot_web_password' => 'nullable|string|max:64',
            'user_herriot_apikey' => 'nullable|string|max:255',
            'user_herriot_issuerid' => 'nullable|string|max:255',
            'user_herriot_serviceid' => 'nullable|string|max:255',
            'user_email' => 'required|string|max:64',
            'user_email_status' => [
                'required',
               // 'string|max:64',
                Rule::enum(SystemStatusConfirmEnum::class)
            ],
            'user_phone' => 'nullable|string|max:18',
            'user_phone_status' => [
                'required',
                //'string|max:64',
                Rule::enum(SystemStatusConfirmEnum::class)
            ],
            'user_notifications' => [
                'required',
               // 'string|max:64',
                Rule::enum(SystemStatusNotificationEnum::class)
            ],
            'user_status' => [
                'required',
               // 'string|max:64',
                Rule::enum(SystemStatusEnum::class)
            ],
            'user_status_delete' => [
                'required',
               // 'string|max:64',
                Rule::enum(SystemStatusDeleteEnum::class)
            ],
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
            'user_base_index' => trans('svr-core-lang::validation'),
            'user_first' => trans('svr-core-lang::validation'),
            'user_middle' => trans('svr-core-lang::validation'),
            'user_last' => trans('svr-core-lang::validation'),
            'user_avatar' => trans('svr-core-lang::validation'),
            'user_password' => trans('svr-core-lang::validation'),
            'user_sex' => trans('svr-core-lang::validation'),
            'user_herriot_login' => trans('svr-core-lang::validation'),
            'user_herriot_password' => trans('svr-core-lang::validation'),
            'user_herriot_web_login' => trans('svr-core-lang::validation'),
            'user_herriot_web_password' => trans('svr-core-lang::validation'),
            'user_herriot_apikey' => trans('svr-core-lang::validation'),
            'user_herriot_issuerid' => trans('svr-core-lang::validation'),
            'user_herriot_serviceid' => trans('svr-core-lang::validation'),
            'user_email' => trans('svr-core-lang::validation'),
            'user_email_status' => trans('svr-core-lang::validation'),
            'user_phone' => trans('svr-core-lang::validation'),
            'user_phone_status' => trans('svr-core-lang::validation'),
            'user_notifications' => trans('svr-core-lang::validation'),
            'user_status' => trans('svr-core-lang::validation'),
            'user_status_delete' => trans('svr-core-lang::validation'),
        ];
    }
}
