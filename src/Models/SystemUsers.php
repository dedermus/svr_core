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
use Svr\Core\Traits\GetTableName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Svr\Core\Traits\GetValidationRules;
use Zebra_Image;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class SystemUsers extends Authenticatable
{
    use GetEnums;
    use GetTableName;
    use GetValidationRules;
    use AuthenticatableTrait;
    use HasFactory;
    use Notifiable;
    use HasApiTokens;


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
     * Постфиксы аватарки
     * @var array|string[]
     */
    protected array $avatarPostfix = [
        '_small',
        '_big',
    ];

    protected string $avatarExp = 'jpg';

    /**
     * Диск хранения
     * @var string
     */
    protected string $diskAvatar = 'local';

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
     * Получить расширение аватара
     * @return string
     */
    public function getAvatarExp(): string
    {
        return $this->avatarExp;
    }

    /**
     * Получить постфиксы аватара
     * @return array|string[]
     */
    public function getAvatarPostfix(): array
    {
        return $this->avatarPostfix;
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
     * @param $size
     * @return string
     */
    public function getUrlAvatar($avatar, $size): string
    {
        if (Storage::exists( $this->getPathAvatar() . $avatar.$size) && !is_null($avatar.$size)) {
            return asset($this->getPathAvatar() . $avatar.$size);
        }
        return admin_asset(config('admin.default_avatar') ?: '/vendor/open-admin/open-admin/gfx/user.svg');
    }

    /**
     * Перехват удаления записи
     * @return bool|null
     */
    public function delete(): ?bool
    {
        $this->mergeAttributesFromCachedCasts();
        $data = $this->attributes;
        $id = $data[$this->primaryKey] ?? null;
        $res = $id ? SystemUsers::findOrFail($id)->toArray() : [];
        $this->eraseAvatar($res['user_avatar']);
        return parent::delete();
    }

    /**
     * Изменяет размер изображения на указанную ширину и высоту.
     *
     * @param string $original_image_name Название исходного файла изображения.
     * @param string $new_message_name    Название измененного файла изображения.
     * @param string $image_path          Путь к файлам изображения.
     * @param int    $width               Новая ширина изображения.
     * @param int    $height              Новая высота изображения.
     */
    public function image_resize(string $original_image_name, string $new_message_name, string $image_path, int $width, int $height): bool|string
    {
        $image = new Zebra_Image();
        $image->source_path = Storage::disk($this->getDiskAvatar())->path($this->getPathAvatar().$original_image_name);
        $image->target_path = Storage::disk($this->getDiskAvatar())->path($this->getPathAvatar().$new_message_name);
        if (!$image->resize($width, $height, ZEBRA_IMAGE_NOT_BOXED)) {
            switch ($image->error) {
                case 1:
                    return 'Файл не существует';
                    break;
                case 2:
                    return 'Файл не является изображением';
                    break;
                case 3:
                    return 'Не удалось сохранить изображение';
                    break;
                case 4:
                    return 'Неподдерживаемый тип исходного изображения';
                    break;
                case 5:
                    return 'Неподдерживаемый тип изменяемого изображения';
                    break;
                case 6:
                    return 'Библиотека GD не поддерживает тип изображения';
                    break;
                case 7:
                    return 'Библиотека GD не установлена';
                    break;
                case 8:
                    return 'Команда "chmod" отключена в конфигурации PHP';
                    break;
                case 9:
                    return 'Функция "exif_read_data" недоступна';
                    break;
            }

            return false;
        }

        return true;
    }

    /**
     * Добавление файла аватара на диск
     * @param $request
     * @return string|null
     */
    public function addFileAvatar($request): ?string
    {
        $filenamebild = null;
        $extention = null;
        if ($request->hasFile('user_avatar')) {
            $this->deleteAvatar($request);
            $filenameWithExt = $request->file('user_avatar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extention = $request->file('user_avatar')->getClientOriginalExtension();
            $filenamebild = $filename . "_" . time() . "." . $extention;
            $fileNameToStore = $this->getPathAvatar() . $filenamebild;
            $request->file('user_avatar')->storeAs($fileNameToStore);

            $image_name_big = str_replace('.' . $extention, '_big.jpg', $filenamebild);
            $image_name_small = str_replace('.' . $extention, '_small.jpg', $filenamebild);

            $this->image_resize($filenamebild, $image_name_big, $this->getPathAvatar(), 800, 800);
            $this->image_resize($filenamebild, $image_name_small, $this->getPathAvatar(), 200, 200);

            if (Storage::exists($this->getPathAvatar() . $filenamebild) && !is_null($filenamebild)) {
                Storage::delete($this->getPathAvatar() . $filenamebild);
            }
        } else {
            $data = $request->all();
            $id = $data[$this->primaryKey] ?? null;
            if ($id) {
                $res = SystemUsers::findOrFail($id)->toArray();
                $filenamebild = $res['user_avatar'] ?? $filenamebild;
            }
        }

        return str_replace('.' . $extention, '', $filenamebild);
    }

    /**
     * Подготовка удаления аватар с диска
     * @param $request
     *
     * @return bool
     */
    public function deleteAvatar($request): bool
    {
        $data = $request->all();
        $id = $data[$this->primaryKey] ?? null;
        $res = $id ? SystemUsers::findOrFail($id)->toArray() : [];
        return $this->eraseAvatar($res['user_avatar']);
    }

    /**
     * Удаление аватара с диска
     * @param $avatar
     *
     * @return bool
     */
    public function eraseAvatar($avatar): bool
    {
        if (empty(trim($avatar))) return false;

        foreach ($this->getAvatarPostfix() as $postfix) {
            $path = $this->getPathAvatar() .$avatar.$postfix.'.'.$this->avatarExp;
            if (Storage::exists( $path)) {
                Storage::delete( $path);
            }
        }
        return true;
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
        $id = $data[$this->primaryKey] ?? null;

        if ($id) {
            $modules_data = $this->find($id);
            if (isset($data['user_avatar'])) {
                $data['user_avatar'] = $this->addFileAvatar($request);
            }
            $modules_data->update($data);
            $user_data = $this->findOrFail($id);
            SystemUsersRoles::userRolesStore($user_data, $data['user_roles_list']);
        }
    }

    /**
     * Получить пользователя по ID.
     *
     * @param int $userId Идентификатор пользователя.
     *
     * @return SystemUsers|null Возвращает объект пользователя или null, если пользователь не найден.
     */
    public static function getUser(int $userId): ?SystemUsers
    {
        return SystemUsers::where([
            ['user_id', '=', $userId],
            ['user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value],
        ])->first();
    }

    /**
     * Получить правила валидации
     * @param Request $request
     * @return array
     */
    private function getValidationRules(Request $request): array
    {
        return [
            $this->primaryKey => [
                $request->isMethod('put') ? 'required' : '',
                Rule::exists('.'.$this->getTable(), $this->primaryKey),
            ],
            'user_base_index' => 'nullable|string|size:7',
            'user_first' => 'nullable|string|min:1|max:32',
            'user_middle' => 'nullable|string|min:1|max:32',
            'user_last' => 'nullable|string|min:1|max:32',
            'user_avatar' => 'file|nullable|mimes:jpeg,jpg,png,gif|max:100',
            'user_password' => 'required|string|min:1|max:64',
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
            'user_email' => 'required|email:rfc,dns|max:64',
            'user_email_status' => [
                'required',
                Rule::enum(SystemStatusConfirmEnum::class)
            ],
            'user_phone' => 'nullable|string|max:18',
            'user_phone_status' => [
                'required',
                Rule::enum(SystemStatusConfirmEnum::class)
            ],
            'user_notifications' => [
                'required',
                Rule::enum(SystemStatusNotificationEnum::class)
            ],
            'user_status' => [
                'required',
                Rule::enum(SystemStatusEnum::class)
            ],
            'user_status_delete' => [
                'required',
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
            'user_avatar' => trans('svr-core-lang::validation.file'),
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

    /**
     * Получить коллекцию аватар пользователя
     *
     * @param $user_id
     * @return array
     */
    public function getCurrentUserAvatar($user_id): array
    {
        $result = [];

        $avatar = SystemUsers::where([
            ['user_id', '=', $user_id],
        ])->first();

        if (!is_null($avatar->user_avatar)) {
            $avatar->toArray();
            $avatarPath = $this->getPathAvatar() . $avatar->user_avatar;
            foreach ($this->avatarPostfix as $postfix) {
                $result['user_avatar' . $postfix] = asset($avatarPath . $postfix . '.' . $this->avatarExp);
            }
        } else {
            foreach ($this->avatarPostfix as $postfix) {
                $result['user_avatar' . $postfix] = null;
            }
        }

        return $result;
    }
}
