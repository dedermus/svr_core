<?php

namespace Svr\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemModulesActions;
use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemRolesRights;
use Svr\Core\Models\SystemUsersToken;
use Svr\Data\Models\DataUsersParticipations;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermissions
{
    /**
     * Проверка прав пользователя.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $tokenData = $this->getTokenData($token);

        if (is_null($tokenData)) {
            return $this->unauthenticatedResponse();
        }

        $participationId = $tokenData['participation_id'];
        $userParticipationInfo = DataUsersParticipations::userParticipationInfo($participationId);

        [$requestModule, $requestAction] = $this->parseRequestRoute();

        if (!$this->checkPermission($requestModule, $requestAction, $userParticipationInfo['role_id'])) {
            return $this->forbiddenResponse();
        }

        $this->setAuthUserData($request, $token, $participationId, $userParticipationInfo);

        return $next($request);
    }

    /**
     * Получить данные токена из базы данных.
     *
     * @param string $token
     *
     * @return mixed
     */
    private function getTokenData(string $token): mixed
    {
        return SystemUsersToken::where([
            ['token_value', '=', $token],
            ['token_status', '=', SystemStatusEnum::ENABLED->value],
        ])->whereNotNull('participation_id')
            ->first();
    }

    /**
     * Возвращает ответ для неаутентифицированного пользователя.
     *
     * @return JsonResponse
     */
    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    /**
     * Возвращает ответ для запрещенного доступа.
     *
     * @return JsonResponse
     */
    private function forbiddenResponse(): JsonResponse
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Разбирает текущий маршрут запроса.
     *
     * @return array
     */
    private function parseRequestRoute(): array
    {
        $routeParts = explode(config('svr.api_prefix'), url()->current());
        $currentRoute = explode('/', ltrim($routeParts[1], '/'));

        return [array_shift($currentRoute), array_shift($currentRoute)];
    }

    /**
     * Устанавливает данные аутентифицированного пользователя.
     *
     * @param Request $request
     * @param string $token
     * @param int $participationId
     * @param array $userParticipationInfo
     */
    private function setAuthUserData(Request $request, string $token, int $participationId, array $userParticipationInfo): void
    {
        $authUserData = Auth::user();
        Config::set('per_page', (int)$request->input('per_page', env('PER_PAGE')));
        Config::set('cur_page', (int)$request->input('cur_page', env('CUR_PAGE')));
        Config::set('max_page', (int)env('MAX_PAGE'));
        Config::set('total_records', 0);
        Config::set('order_field', $request->input('order_field', null));
        Config::set('order_direction', $request->input('order_direction', env('ORDER_DIRECTION')));
        $authUserData['token'] = $token;
        $authUserData['participation_id'] = $participationId;
        $authUserData['user_participation_info'] = $userParticipationInfo;

        foreach ($userParticipationInfo as $key => $item) {
            $authUserData[$key] = $item;
        }
    }

    /**
     * Проверяет, имеет ли пользователь необходимое разрешение для доступа к определённому модулю и действию.
     *
     * @param string $requestModule
     * @param string $requestAction
     * @param int $roleId
     *
     * @return bool
     */
    public function checkPermission(string $requestModule, string $requestAction, int $roleId): bool
    {
        $roleSlug = SystemRoles::find($roleId)->role_slug;
        $roleRightsList = SystemRolesRights::where('role_slug', $roleSlug)
            ->pluck('right_slug')
            ->toArray();

        $requestRight = SystemModulesActions::where('module_slug', $requestModule)
            ->where('right_action', $requestAction)
            ->value('right_slug');

        return in_array($requestRight, $roleRightsList);
    }
}
