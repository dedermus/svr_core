<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemRolesRights extends Model
{
    use HasFactory;

	/**
	 * Точное название таблицы с учетом схемы
	 * @var string
	 */
	protected $table								= 'system.system_roles_rights';

	/**
	 * Первичный ключ таблицы
	 * @var string
	 */
	protected $primaryKey							= 'role_right_id';

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
		'role_slug',								// ROLE_SLUG в таблице SYSTEM.SYSTEM_ROLES */
		'right_slug',								// RIGHT_SLUG в таблице SYSTEM.SYSTEM_MODULES_ACTIONS */
		'created_at',					            // Дата и время создания */
		'updated_at',								// Дата и время модификации */
	];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Получить связь ролей и прав
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function roles()
	{
		return $this->belongsTo(SystemRoles::class, 'role_slug', 'role_slug');
	}

	/**
	 * Получение прав роли
	 */
	public static function roleRightsGet($role_id)
	{
		$role_data					= systemRoles::find($role_id);

		if($role_data)
		{
			$role_rights_list		= self::leftJoin('system.system_modules_actions', function($join) {
				$join->on('system_modules_actions.right_slug', '=', 'system_roles_rights.right_slug');
			})->where('role_slug', $role_data['role_slug'])->get();

			if($role_rights_list->count() > 0)
			{
				return array_column($role_rights_list->toArray(), 'right_id');
			}else{
				return [];
			}
		}else{
			return [];
		}
	}

    /**
     * Установка прав роли
     *
     * @param $role_data
     * @param $rights_list
     *
     * @return void
     */
    public static function roleRightsStore($role_data, $rights_list): void
    {
        self::where('role_slug', $role_data->role_slug)->delete();

        if($rights_list->role_rights_list && is_array($rights_list->role_rights_list) && count($rights_list->role_rights_list) > 0)
        {
            foreach ($rights_list->role_rights_list as $right_id)
            {
                if((int)$right_id > 0)
                {
                    $right_data	= SystemModulesActions::find($right_id);

                    if($right_data)
                    {
                        self::firstOrCreate([
                            'role_slug'		=> $role_data->role_slug,
                            'right_slug'	=> $right_data->right_slug,
                        ]);
                    }
                }
            }
        }
    }
}
