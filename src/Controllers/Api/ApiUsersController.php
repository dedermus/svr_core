<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Auth\Events\Validated;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthInfoSystemUsersResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ApiUsersController extends Controller
{
    /**
     * Создание новой записи.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $model = new SystemUsers();
        $record = $model->userCreate($request);
        return response()->json($record, 201);
    }

    /**
     * Обновление существующей записи.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $record = new SystemUsers();
        $request->setMethod('PUT');
        $record->userUpdate($request);
        return response()->json($record);
    }

    /**
     * Получение списка записей с пагинацией.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15); // Количество записей на странице по умолчанию
        $records = SystemUsers::paginate($perPage);
        return response()->json($records);
    }

    /**
     * Получение информации о пользователе.
     *
     * @return AuthInfoSystemUsersResource
     */
    public function show_auth_info()
    {
        /** @var  $user - получим авторизированного пользователя */
        $user = auth()->user();
        //dd($_SERVER);
        dd($user);
        $record = SystemUsers::where('user_id', $user->user_id)->first();
        return new AuthInfoSystemUsersResource($record);
    }

    public function authLogin(Request $request): JsonResponse
    {
        $model = new SystemUsers();
        $user = null; // прееменная для пользователя
        $filterKeys = ['user_email', 'user_password'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);

        $credentials = $request->only(['user_email', 'user_password']);

        // Проверить существование пользователя
        /** @var SystemUsers $user */
        $users = SystemUsers::where('user_email', $credentials['user_email'])->get();


        if (!is_null($users)) {
            foreach ($users as &$item) {
                if ($item && Hash::check($credentials['user_password'], $item->user_password)) {
                    $user = $item;
                    break;
                }
            }
        }

        if (is_null($user)) {
            return response()->json(['error' => 'Неправильный логин или пароль'], 401);
        }
        // Выдать токен пользователю
        $token = $user->createToken('auth_token')->plainTextToken;

        $request->merge([
            'user_id' => $user->user_id,
            'participation_id' => null,
            'token_value' => $user->createToken('auth_token')->plainTextToken,
            'token_client_ip' => $request->ip(),
            'token_client_agent' => $request->header('User-Agent'),
            'browser_name' => null,
            'browser_version' => null,
            'platform_name' => null,
            'platform_version' => null,
            'device_type' => 'desktop',
            'token_last_login' => getdate()[0],
            'token_last_action' => getdate()[0],
            'token_status' => SystemStatusEnum::ENABLED->value,
        ]);
        (new SystemUsersToken)->userTokenCreate($request);

        // Выдать токен пользователю
        $token = $user->createToken('auth_token')->plainTextToken;





        return response()->json(['token' => $token]);


    }

    /**
     * Return a validation error response.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return JsonResponse
     */
    private function validationErrorResponse($validator): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors()
        ], 401);
    }
}
