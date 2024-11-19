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
     * @param int $user_id - атрибут user_id из таблицы system.system_users
     *
     * @return array
     */
    public static function userRolesGet($user_id): array
    {
        // Проверяем, существует ли пользователь
        if (!SystemUsers::find($user_id)) {
            return [];
        }

        // Получаем список ролей пользователя
        $userRolesList = self::leftJoin('system.system_roles', 'system.system_roles.role_slug', '=', 'system.system_users_roles.role_slug')
            ->where('user_id', $user_id)
            ->pluck('role_id')
            ->toArray();

        return $userRolesList;
    }

    /**
     * Получение ролей пользователя
     *
     * @param int $user_id - атрибут user_id из таблицы system.system_users
     * @return array
     */
    public static function userRolesList($user_id): array
    {
        // Проверяем, существует ли пользователь
        if (!SystemUsers::find($user_id)) {
            return [];
        }

        // Получаем список ролей пользователя
        $userRolesList = self::leftJoin('system.system_roles', 'system.system_roles.role_slug', '=', 'system.system_users_roles.role_slug')
            ->where('user_id', $user_id)
            ->get()
            ->toArray();

        return $userRolesList;
    }

    /**
     * Установка ролей пользователя
     *
     * @param object $user_data - сущность пользователя
     * @param array $roles_list - массив role_id из таблицы system.system_roles
     *
     * @return void
     */
    public static function userRolesStore($user_data, array $roles_list): void
    {
        // Удаляем все текущие роли пользователя
        self::where('user_id', $user_data->user_id)->delete();

        // Проверяем, что список ролей не пустой
        if (empty($roles_list)) {
            return;
        }

        // Получаем данные ролей из базы данных
        $roles = SystemRoles::whereIn('role_id', $roles_list)->get();

        // Создаем новые записи для каждой роли
        foreach ($roles as $role) {
            self::firstOrCreate([
                'user_id' => $user_data->user_id,
                'role_slug' => $role->role_slug,
            ]);
        }
    }
}
