<?php

namespace Svr\Core\Models;

use Svr\Core\Traits\GetEnums;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUsersRoles extends Model
{
    use GetEnums;

    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_users_roles';


    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'user_role_id';


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
     * Поля, которые можно менять сразу массивом
     *
     * @var array
     */
    protected $fillable
        = [
            'user_id',
            'role_slug',
            'created_at',
            'updated_at',
        ];

    protected array $dates
        = [
            'created_at',
            'updated_at',
        ];

    /**
     * Получение ролей пользователя
     *
     * @param $user_id - аттрибут user_id из таблицы system.system_users
     *
     * @return array
     */
    public static function userRolesGet($user_id): array
    {
        $user_data = SystemUsers::find($user_id);

        if ($user_data) {
            $user_roles_list = self::leftJoin('system.system_roles', function ($join) {
                $join->on('system.system_roles.role_slug', '=', 'system.system_users_roles.role_slug');
            })->where('user_id', $user_data['user_id'])->get();

            if ($user_roles_list->count() > 0) {
                return array_column($user_roles_list->toArray(), 'role_id');
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * Установка ролей пользователя
     *
     * @param $user_data    - сущность пользователя
     * @param $roles_list   - массив role_id из таблицы system.system_roles
     *
     * @return void
     */
    public static function userRolesStore($user_data, $roles_list): void
    {
        self::where('user_id', $user_data->user_id)->delete();

        if ($roles_list && is_array($roles_list) && count($roles_list) > 0) {
            foreach ($roles_list as $role_id) {
                if ((int)$role_id > 0) {
                    $role_data = SystemRoles::find($role_id);

                    if ($role_data) {
                        self::firstOrCreate([
                            'user_id'   => $user_data->user_id,
                            'role_slug' => $role_data->role_slug,
                        ]);
                    }
                }
            }
        }
    }
}
