<?php

namespace Svr\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckUserPermissions
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        // Получаем текущего аутентифицированного пользователя. Попробовать auth()->user()
        // Вернёт модель SystemUsers
        $user = auth()->user();

        // Проверяем, есть ли у пользователя необходимые права
        // Пример на коленке
        if ($user->getPrimaryKey() != 'user_id') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Если всё прошло успешно, передаем запрос дальше
        return $next($request);
    }
}
