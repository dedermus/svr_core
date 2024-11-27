<?php

namespace Svr\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemModulesActions;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemRolesRights;
use Svr\Core\Models\SystemUsersToken;
use Svr\Data\Models\DataUsersParticipations;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermissions
{
    /**
     * Проверка прав пользователя
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // получим токен текущего пользователя
        $token = $request->bearerToken();
        // получим данные о токене из базы
        $token_data = SystemUsersToken::where([
            ['token_value', '=', $token],
            ['token_status', '=', SystemStatusEnum::ENABLED->value],
        ])->whereNotNull('participation_id')
            ->first();

        // если токен не найден или DISABLED
        if (is_null($token_data)) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        } else {
            // иначе преобразуем результат в массив
            $token_data->toArray();
        }

        // - запомнили participation_id
        $participation_id = $token_data['participation_id'];
        // - получили привязки пользователя
        $user_participation_info = DataUsersParticipations::userParticipationInfo($participation_id);

        $current_route = explode('/', url()->current());
        $request_action = array_pop($current_route);
        $request_module = array_pop($current_route);

        if (!$this->checkPermission($request_module, $request_action, $user_participation_info['role_id'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Если всё прошло успешно, передаем запрос дальше
        $authUserData = Auth::user();
        $authUserData['token'] = $token;
        foreach ($user_participation_info as $key => $item)
        {
            $authUserData[$key] = $item;
        }
        return $next($request);
    }

    /**
     * Проверяет, имеет ли пользователь необходимое разрешение для доступа к определённому модулю и действию.
     *
     * @param $request_module   - Слаг запрошенного модуля.
     * @param $request_action   - Действие, запрошенное в модуле.
     * @param $role_id          - ID роли пользователя.
     *
     * @return bool             - True, если пользователь имеет необходимое разрешение, false в противном случае.
     */
    public function checkPermission($request_module, $request_action, $role_id): bool
    {
        $role_rights_list = SystemRolesRights::where('role_slug', '=', SystemRoles::find($role_id)->only('role_slug'))
            ->get()->pluck('right_slug')->toArray();

        $request_right = SystemModulesActions::where('module_slug', '=', $request_module)->where(
            'right_action', '=', $request_action
        )->value('right_slug');

        return in_array($request_right, $role_rights_list);
    }
}
