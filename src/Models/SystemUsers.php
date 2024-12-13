<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Svr\Core\Enums\SystemParticipationsTypesEnum;
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
use Svr\Data\Models\DataCompanies;
use Svr\Data\Models\DataCompaniesLocations;
use Svr\Data\Models\DataUsersParticipations;
use Svr\Directories\Models\DirectoryCountriesRegion;
use Svr\Directories\Models\DirectoryCountriesRegionsDistrict;
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
     * Получить путь до папки с аватарками пользователя
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

            $image_name_big = str_replace('.' . $extention, '_big.'.$this->getAvatarExp(), $filenamebild);
            $image_name_small = str_replace('.' . $extention, '_small.'.$this->getAvatarExp(), $filenamebild);

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
     * Отношение Пользователь принадлежит ко многим ролям.
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
     * Отношение `participations`, которое связывает пользователя с его участиями.
     *
     * @return HasMany
     */
    public function participations(): HasMany
    {
        return $this->hasMany(DataUsersParticipations::class, 'user_id', 'user_id');
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
     * Получить пользователей по их ID  в виде массива
     * @param array $userListId
     *
     * @return array Возвращает массив пользователей или null, если ни один пользователь не найден.
     */
    public static function getListUser(array $userListId): array
    {
        $data = null;
        $result = SystemUsers::query()
            ->where('user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value)
            ->whereIn('user_id', $userListId)
            ->get()->toArray();

        if (!is_null($result)) {
            $data = [];
            foreach ($result as $item) {
                $data[$item['user_id']] = $item;
                $data[$item['user_id']]['user_companies_count'] = self::getUserCompaniesCount($item['user_id']);
                $avatars = (new SystemUsers)->getCurrentUserAvatar($item['user_id']);
                $data[$item['user_id']]['user_avatar_small'] = $avatars['user_avatar_small'];
                $data[$item['user_id']]['user_avatar_big'] = $avatars['user_avatar_big'];
                unset($data[$item['user_id']]['user_avatar']);
            }
        }
        return $data;
    }

    /**
     * @param $user_id - ID пользователя
     *
     * @return int
     */
    public static function getUserCompaniesCount($user_id): int
    {
        return DataUsersParticipations::query()
            ->where([
                ['user_id', $user_id],
                ['participation_item_type', SystemParticipationsTypesEnum::COMPANY->value]
            ])
            ->count();
    }

    /**
     * Получить пагинированный список пользователей с набором передаваемых параметров и поисковой строкой
     *
     * @param int    $per_page      Количество записей на странице.
     * @param int    $cur_page      Текущая страница.
     * @param bool   $only_enabled  Флаг выборки, учитывающий только не заблокированных и не удаленных пользователей
     * @param array  $filters_list  Фильтр
     * @param string $search_string Строка поиска
     *
     * @return array
     */
    public function users_list(int $per_page, int $cur_page, bool $only_enabled = true, array $filters_list = [], string $search_string = ''): array
    {
        $searchTerms = explode(' ', $search_string); // Разбиваем строку на массив слов

        $users = SystemUsers::withCount(['participations as user_companies_count' => function ($query) {
            $query->where('participation_item_type', 'company');
        }])
            ->addSelect(
                'system_users.*',
                DB::raw("CONCAT(user_first, ' ', user_middle, ' ', user_last) AS user_full_name"),
                "up.participation_id",
                "up.participation_item_id",
                "up.participation_status",
                "c.company_id",
                "c.company_name_short",
                "c.company_name_full",
                "c.company_status",
                "c.company_base_index",
                "cl.company_location_id",
                "company_r.region_name AS company_region_name",
                "company_r.region_id AS company_region_id",
                "company_rd.district_name AS company_district_name",
                "company_rd.district_id AS company_district_id",
                "district_r.region_name AS district_region_name",
                "district_r.region_id AS district_region_id",
                "district_rd.district_name AS district_district_name",
                "district_rd.district_id AS district_district_id",
                "region_r.region_name AS region_region_name",
                "region_r.region_id AS region_region_id",
                "r.role_id",
                "r.role_name_long",
                "r.role_name_short",
                "r.role_slug",
                "r.role_status"
            )
            ->leftJoin(DataUsersParticipations::getTableName().' AS up', 'up.user_id', '=', 'system_users.user_id')
            ->leftJoin(DataCompaniesLocations::getTableName() . ' AS cl', function ($join) {
                $join->on('cl.company_location_id', '=', 'up.participation_item_id')
                    ->where('up.participation_item_type', '=', SystemParticipationsTypesEnum::COMPANY->value);
            })
            ->leftJoin(DataCompanies::getTableName().' AS c', 'c.company_id', '=', 'cl.company_id')
            ->leftJoin(DirectoryCountriesRegion::getTableName().' AS company_r', 'company_r.region_id', '=', 'cl.region_id')
            ->leftJoin(DirectoryCountriesRegionsDistrict::getTableName().' AS company_rd', 'company_rd.district_id', '=', 'cl.district_id')
            ->leftJoin(DirectoryCountriesRegionsDistrict::getTableName() . ' AS district_rd', function ($join) {
                $join->on('district_rd.district_id', '=', 'up.participation_item_id')
                    ->where('up.participation_item_type', '=', SystemParticipationsTypesEnum::DISTRICT->value);
            })
            ->leftJoin(DirectoryCountriesRegion::getTableName().' AS district_r', 'district_r.region_id', '=', 'district_rd.region_id')
            ->leftJoin(DirectoryCountriesRegion::getTableName() . ' AS region_r', function ($join) {
                $join->on('region_r.region_id', '=', 'up.participation_item_id')
                    ->where('up.participation_item_type', '=', SystemParticipationsTypesEnum::REGION->value);
            })
            ->leftJoin(SystemUsersRoles::getTableName().' AS ur', 'ur.user_id', '=', 'system_users.user_id')
            ->leftJoin(SystemRoles::getTableName().' AS r', 'r.role_slug', '=', 'ur.role_slug')
            ->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->orWhere(function ($subQuery) use ($term) {
                        $subQuery
                            ->where('user_first', 'ILIKE', "%{$term}%")
                            ->orWhere('system_users.user_id', 'ILIKE', "%{$term}%")
                            ->orWhere('system_users.user_first', 'ILIKE', "%{$term}%")
                            ->orWhere('system_users.user_middle', 'ILIKE', "%{$term}%")
                            ->orWhere('system_users.user_last', 'ILIKE', "%{$term}%")
                            ->orWhere(DB::raw("CONCAT(system_users.user_first, ' ', system_users.user_middle, ' ', system_users.user_last)"), 'ILIKE', "%{$term}%")
                            ->orWhere(DB::raw("to_char(system_users.user_date_created, 'DD.MM.YYYY')"), 'ILIKE', "%{$term}%")
                            ->orWhere(DB::raw("to_char(system_users.user_date_block, 'DD.MM.YYYY')"), 'ILIKE', "%{$term}%")
                            ->orWhere('system_users.user_status', 'ILIKE', "%{$term}%")
                            ->orWhere('system_users.user_email', 'ILIKE', "%{$term}%")
                            ->orWhere('c.company_name_short', 'ILIKE', "%{$term}%")
                            ->orWhere('c.company_name_full', 'ILIKE', "%{$term}%")
                            ->orWhere('r.role_name_short', 'ILIKE', "%{$term}%")
                            ->orWhere('r.role_name_long', 'ILIKE', "%{$term}%")
                            ->orWhere('company_rd.district_name', 'ILIKE', "%{$term}%")
                            ->orWhere('district_rd.district_name', 'ILIKE', "%{$term}%")
                            ->orWhere('company_r.region_name', 'ILIKE', "%{$term}%")
                            ->orWhere('district_r.region_name', 'ILIKE', "%{$term}%")
                            ->orWhere('region_r.region_name', 'ILIKE', "%{$term}%")
                            ->orWhere('c.company_base_index', 'ILIKE', "%{$term}%");
                    });
                }
            })
            ->whereRaw(SystemUsers::createFilterSql($filters_list));
        // Добавляем условия, если $only_enabled равно true
        if ($only_enabled) {
            $users->where('user_status_delete', SystemStatusDeleteEnum::ACTIVE->value)  // присутствует при $only_enabled = true
            ->where('user_status', SystemStatusEnum::ENABLED->value);                   // присутствует при $only_enabled = true
        }
        $users->orderBy('system_users.user_id')
            ->orderBy('user_full_name')
            ->orderBy('district_district_name')
            ->orderBy('region_region_name')
            ->distinct('system_users.user_id');

        $results = $users->paginate($per_page, ['*'], 'page', $cur_page);
        Config::set('total_records', $results->total());
        // если есть список
        if (Config::get('total_records') > 0) {
            foreach ($results as $result) {
                // добавим пути к аватаркам
                $avatars = (new SystemUsers)->getCurrentUserAvatar($result['user_id']);
                $result['user_avatar_small'] = $avatars['user_avatar_small'];
                $result['user_avatar_big'] = $avatars['user_avatar_big'];
                unset($result['user_avatar']);
            }
        }

        return $results->toArray()['data'];
    }

    /**
     * Формирование фильтра для запроса
     * @param array $filters_list
     *
     * @return string
     */
    private static function createFilterSql(array $filters_list): string
    {
        if (isset($filters_list['user_date_block_min'])) {
            $filters_list['user_date_block_min'] = date('Y-m-d', strtotime($filters_list['user_date_block_min']));
        }
        if (isset($filters_list['user_date_block_max'])) {
            $filters_list['user_date_block_max'] = date('Y-m-d', strtotime($filters_list['user_date_block_max']));
        }
        if (isset($filters_list['user_date_register_min'])) {
            $filters_list['user_date_register_min'] = date('Y-m-d', strtotime($filters_list['user_date_register_min']));
        }
        if (isset($filters_list['user_date_register_max'])) {
            $filters_list['user_date_register_max'] = date('Y-m-d', strtotime($filters_list['user_date_register_max']));
        }

        $filters_mapping = [];

        if (isset($filters_list['user_id'])) {
            $filters_mapping['user_id'] = "system_users.user_id = " . $filters_list['user_id'];
        }
        if (isset($filters_list['user_full_name'])) {
            $filters_mapping['user_full_name'] = "lower(CONCAT(user_first, ' ', user_middle, ' ', user_last)) ILIKE '%" . $filters_list['user_full_name'] . "%'";
        }
        if (isset($filters_list['district_id'])) {
            $filters_mapping['district_id'] = 'district_rd.district_id IN (' . implode(',', $filters_list['district_id']) . ')';
        }
        if (isset($filters_list['company_location_id'])) {
            $filters_mapping['company_location_id'] = 'cl.company_location_id IN (' . implode(',', $filters_list['company_location_id']) . ')';
        }
        if (isset($filters_list['company_id'])) {
            $filters_mapping['company_id'] = 'c.company_id IN (' . implode(',', $filters_list['company_id']) . ')';
        }
        if (isset($filters_list['region_id'])) {
            $filters_mapping['region_id'] = 'region_r.region_id IN (' . implode(',', $filters_list['region_id']) . ')';
        }
        if (isset($filters_list['user_date_block_min'])) {
            $filters_mapping['user_date_block_min'] = "system_users.user_date_block >= '" . $filters_list['user_date_block_min'] . "'";
        }
        if (isset($filters_list['user_date_block_max'])) {
            $filters_mapping['user_date_block_max'] = "system_users.user_date_block <= '" . $filters_list['user_date_block_max'] . "'";
        }
        if (isset($filters_list['user_date_register_min'])) {
            $filters_mapping['user_date_register_min'] = "system_users.user_date_created >= '" . $filters_list['user_date_register_min'] . "'";
        }
        if (isset($filters_list['user_date_register_max'])) {
            $filters_mapping['user_date_register_max'] = "system_users.user_date_created <= '" . $filters_list['user_date_register_max'] . "'";
        }
        if (isset($filters_list['role_id'])) {
            $filters_mapping['role_id'] = 'role_id IN (' . implode(',', $filters_list['role_id']) . ')';
        }
        if (isset($filters_list['user_status'])) {
            $filters_mapping['user_status'] = 'system_users.user_status = \'' . $filters_list['user_status'] . '\'';
        }
        $filters_mapping['1'] = '1 = 1';

        // Объединяем все условия в строку с использованием 'AND'
        return implode(' AND ', $filters_mapping);
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
