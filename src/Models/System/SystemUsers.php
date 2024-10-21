<?php

namespace Svr\Core\Models\System;

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
     *
     * @param $avatar
     *
     * @return string
     */
    public function getUrlAvatar($avatar): string
    {
        // если файл аватар существует
        if (Storage::exists($this->getDiskAvatar().$this->getPathAvatar().$avatar) && !is_null($avatar)) {
            return asset($this->getPathAvatar().$avatar);
        }

        // если аватар не найден или не задавался
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
        // получаем массив полей и значений из формы
        $data = $this->attributes;
        // получаем id
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
        $res = (is_null($id)) ? [] : SystemUsers::findOrFail($id)->toArray();
        $avatar = (isset($res['user_avatar'])) ? $res['user_avatar'] : null;
        // если файл аватар существует
        if (Storage::exists($this->getDiskAvatar().$this->getPathAvatar().$avatar) && !is_null($avatar)) {
            Storage::delete($this->getDiskAvatar().$this->getPathAvatar().$avatar);
            return true;
        }

        return parent::delete(); // TODO: Change the autogenerated stub
    }

    /**
     * Добавление файла аватара на диск
     * @param $request
     *
     * @return string
     */
    /**
     * @param $request
     *
     * @return string|null
     */
    public function addFileAvatar($request): ?string
    {
        $filenamebild = null;

        // если выбран файл для загрузки
        if(!is_null($request->file('user_avatar'))) {
            // удаляем предыдущий аватар
            $this->deleteAvatar($request);
            // Имя и расширение файла
            $filenameWithExt = $request->file('user_avatar')->getClientOriginalName();
            // Только оригинальное имя файла
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Расширение
            $extention = $request->file('user_avatar')->getClientOriginalExtension();
            // Сборное имя файл
            $filenamebild = $filename . "_" . time() . "." . $extention;
            // Путь для сохранения
            $fileNameToStore = $this->getPathAvatar() . $filenamebild;
            // Сохраняем файл
            $request->file('user_avatar')->storeAs($this->getDiskAvatar(), $fileNameToStore);
        }

        if(is_null($request->file('user_avatar'))) {
            // получаем массив полей и значений из формы
            $data = $request->all();
            // получаем id
            $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
            $res = SystemUsers::findOrFail($id)->toArray();
            $filenamebild = (isset($res['user_avatar'])) ? $res['user_avatar'] : $filenamebild;
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
        // получаем массив полей и значений из формы
        $data = $request->all();
        // получаем id
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
        $res = (is_null($id)) ? [] : SystemUsers::findOrFail($id)->toArray();
        $avatar = (isset($res['user_avatar'])) ? $res['user_avatar'] : null;
        // если файл аватар существует
        if (Storage::exists($this->getDiskAvatar().$this->getPathAvatar().$avatar) && !is_null($avatar)) {
            Storage::delete($this->getDiskAvatar().$this->getPathAvatar().$avatar);
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
     * @param $request
     *
     * @return void
     */
    public function userCreate($request): void
    {
        $this->rules($request);
        // сохраняем аватар
        $user_avatar = $this->addFileAvatar($request);
        $data = $request->all();
        $data['user_avatar'] = $user_avatar;
        $this->fill($data);
        $this->save();
        $user_data = $this->find($this->getKey()); // получим данные по новой записи
        $data = $request->all();
        // сохраним роли пользователя, если они выбраны
        if(isset($data['user_roles_list']) && $user_data)
        {
            SystemUsersRoles::userRolesStore($user_data, $data['user_roles_list']);
        }
     }

    /**
     * Обновить запись
     *
     * @param $request
     *
     * @return void
     */
    public function userUpdate($request): void
    {
        // валидация
        self::rules($request);
        // сохраняем аватар
        $user_avatar = $this->addFileAvatar($request);
        // получаем массив полей и значений из формы
        $data = $request->all();
        $data['user_avatar'] = $user_avatar;
        // получаем id
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
        // готовим сущность для обновления
        $modules_data = $this->find($id);
        // обновляем запись
        $modules_data->update($data);
        $user_data = $this->findOrFail($id);
        // обновим связь пользователя и ролей
        SystemUsersRoles::userRolesStore($user_data, $data['user_roles_list']);
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

        // user_base_index - Базовый индекс хозяйства
        $request->validate(
            ['user_base_index' => 'nullable|string|size:7'],
            ['user_base_index' => trans('svr-core-lang::validation')],
        );

        // user_first - Имя пользователя
        $request->validate(
            ['user_first' => 'nullable|string|min:1|max:32'],
            ['user_first' => trans('svr-core-lang::validation')],
        );

        // user_middle - Отчество пользователя
        $request->validate(
            ['user_middle' => 'nullable|string|min:1|max:32'],
            ['user_middle' => trans('svr-core-lang::validation')],
        );

        // user_last - Фамилия пользователя
        $request->validate(
            ['user_last' => 'nullable|string|min:1|max:32'],
            ['user_last' => trans('svr-core-lang::validation')],
        );

        // user_avatar
        $request->validate(
            ['user_avatar' => 'image|nullable|mimes:jpeg,jpg,png,gif|max:10000'],
            ['user_avatar' => trans('svr-core-lang::validation')],
        );

        // user_password - Пароль пользователя
        $request->validate(
            ['user_password' => 'required|min:1|max:64'],
            ['user_password' => trans('svr-core-lang::validation')],
        );

        // user_sex - Пол пользователя
        $request->validate(
            ['user_sex' => 'required'],
            ['user_sex' => trans('svr-core-lang::validation')],
        );

        // user_herriot_login - логин в API Хорриот пользователя
        $request->validate(
            ['user_herriot_login' => 'nullable|string|max:64'],
            ['user_herriot_login' => trans('svr-core-lang::validation')],
        );

        // user_herriot_password - Пароль в API Хорриот пользователя
        $request->validate(
            ['user_herriot_password' => 'nullable|string|max:64'],
            ['user_herriot_password' => trans('svr-core-lang::validation')],
        );

        // user_herriot_web_login - Логин в WEB Хорриот пользователя
        $request->validate(
            ['user_herriot_web_login' => 'nullable|string|max:64'],
            ['user_herriot_web_login' => trans('svr-core-lang::validation')],
        );

        // user_herriot_web_password - Пароль в WEB Хорриот пользователя
        $request->validate(
            ['user_herriot_web_password' => 'nullable|string|max:64'],
            ['user_herriot_web_password' => trans('svr-core-lang::validation')],
        );

        // user_herriot_apikey - APIKey Хорриот пользователя
        $request->validate(
            ['user_herriot_apikey' => 'nullable|string|max:255'],
            ['user_herriot_apikey' => trans('svr-core-lang::validation')],
        );

        // user_herriot_issuerid - IssuerId Хорриот пользователя
        $request->validate(
            ['user_herriot_issuerid' => 'nullable|string|max:255'],
            ['user_herriot_issuerid' => trans('svr-core-lang::validation')],
        );

        // user_herriot_serviceid - ServiceId Хорриот пользователя
        $request->validate(
            ['user_herriot_serviceid' => 'nullable|string|max:255'],
            ['user_herriot_serviceid' => trans('svr-core-lang::validation')],
        );

        // user_email - Email пользователя
        $request->validate(
            ['user_email' => 'required|string|max:64'],
            ['user_email' => trans('svr-core-lang::validation')],
        );

        // user_email_status - Статус электронного адреса
        $request->validate(
            ['user_email_status' => 'string|max:64'],
            ['user_email_status' => trans('svr-core-lang::validation')],
        );

        // user_phone - Телефон пользователя
        $request->validate(
            ['user_phone' => 'nullable|string|max:18'],
            ['user_phone' => trans('svr-core-lang::validation')],
        );

        // user_phone_status - Статус телефона
        $request->validate(
            ['user_phone_status' => 'string|max:64'],
            ['user_phone_status' => trans('svr-core-lang::validation')],
        );

        // user_notifications - Подтверждение
        $request->validate(
            ['user_notifications' => 'string|max:64'],
            ['user_notifications' => trans('svr-core-lang::validation')],
        );

        // user_status - Статус записи (активна/не активна)
        $request->validate(
            ['user_status' => 'string|max:64'],
            ['user_status' => trans('svr-core-lang::validation')],
        );

        // user_status_delete - Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)
        $request->validate(
            ['user_status_delete' => 'string|max:64'],
            ['user_status_delete' => trans('svr-core-lang::validation')],
        );
    }

}
