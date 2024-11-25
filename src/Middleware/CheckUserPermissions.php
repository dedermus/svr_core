<?php

namespace Svr\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Svr\Core\Models\SystemModulesActions;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemRolesRights;
use Svr\Core\Models\SystemUsersToken;
use Svr\Data\Models\DataUsersParticipations;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        //Получим токен текущего пользователья
        $token = $request->bearerToken();
        //Получим данные о токене из базы
        $token_data = SystemUsersToken::where('token_value', '=', $token)->first()->toArray();
        //Если токен не нашелся - уходим
        if (!$token_data || !isset($token_data['participation_id']))
        {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        //запомнили participation_id
        $participation_id = $token_data['participation_id'];
        //получили привязки пользователя
        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);

        $current_route = explode('/', url()->current());
        $request_action = array_pop($current_route);
        $request_module = array_pop($current_route);

        if (!$this->checkPermission($request_module, $request_action, $user_participation_info['role_id']))
        {
            return response()->json(['error' => 'Forbiddden'], 403);
        }

        // Если всё прошло успешно, передаем запрос дальше
        return $next($request);
    }

    /**
     * Проверяет, имеет ли пользователь необходимое разрешение для доступа к определённому модулю и действию.
     *
     * @param string $request_module Слаг запрошенного модуля.
     * @param string $request_action Действие, запрошенное в модуле.
     * @param int $role_id ID роли пользователя.
     *
     * @return bool True, если пользователь имеет необходимое разрешение, false в противном случае.
     */
    public function checkPermission($request_module, $request_action, $role_id)
    {
        $role_rights_list = SystemRolesRights::where('role_slug', '=', SystemRoles::find($role_id)->only('role_slug'))->get()->pluck('right_slug')->toArray();

        $request_right = SystemModulesActions::where('module_slug', '=', $request_module)->where('right_action', '=', $request_action)->value('right_slug');

        return in_array($request_right, $role_rights_list);
    }
}
