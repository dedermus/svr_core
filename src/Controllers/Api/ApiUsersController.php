<?php

namespace Svr\Core\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersToken;
use Svr\Core\Resources\AuthInfoSystemUsersResource;
use Illuminate\Support\Facades\Hash;
use hisorange\BrowserDetect\Parser as Browser;
use Svr\Data\Models\DataUsersParticipations;

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


    /**
     * @param Request $request
     *
     * @return JsonResponse|AuthInfoSystemUsersResource
     */
    public function authLogin(Request $request)
    {
        $model = new SystemUsers();
        $user = null; // прееменная для пользователя
        $filterKeys = ['user_email', 'user_password'];
        $rules = $model->getFilterValidationRules($request, $filterKeys);
        $messages = $model->getFilterValidationMessages($filterKeys);
        $request->validate($rules, $messages);

        $credentials = $request->only(['user_email', 'user_password']);

        // Проверить существование пользователя, который активный и не удален
        /** @var SystemUsers $user */
        $users = SystemUsers::where([
            ['user_email', '=', $credentials['user_email']],
            ['user_status', '=', SystemStatusEnum::ENABLED->value],
            ['user_status_delete', '=', SystemStatusDeleteEnum::ACTIVE->value],
        ])->get();

        // Если получен список пользователей с одним email
        if (!is_null($users)) {
            // переберем пользователей
            foreach ($users as &$item) {
                // если email и password совпали
                if ($item && Hash::check($credentials['user_password'], $item->user_password)) {
                    $user = $item;
                    break;  // выйдем из перебора
                }
            }
            unset($item);
        }
        // Если пользователь не найден
        if (is_null($user)) {
            return response()->json(['error' => 'Неправильный логин или пароль'], 401);
        }
        // Выдать токен пользователю
        $token = $user->createToken('auth_token')->plainTextToken;

        $last_token = SystemUsersToken::userTokenData($user->user_id);

        $participation_id = null;
        if ($last_token)
        {
            $last_token = (array)$last_token;
            $participation_id = $last_token['participation_id'] ?? null;

            if (!is_null($participation_id))
            {
                //TODO: по participation_id подтянуть (используя реляции или джоины) роль пользователя для того, чтобы сделать ее активной в справочниках
                $user_roles = DataUsersParticipations::userRolesData();
            }
        }


        $request->merge([
            'user_id'            => $user->user_id,
            'participation_id'   => $participation_id,
            'token_value'        => $token,
            'token_client_ip'    => $request->ip(),
            'token_client_agent' => Browser::userAgent(),//$request->header('User-Agent'),
            'browser_name'       => Browser::browserFamily(),
            'browser_version'    => Browser::browserVersion(),
            'platform_name'      => Browser::platformFamily(),
            'platform_version'   => Browser::platformVersion(),
            'device_type'        => strtolower(Browser::deviceType()),
            'token_last_login'   => getdate()[0],
            'token_last_action'  => getdate()[0],
            'token_status'       => SystemStatusEnum::ENABLED->value,
            ...$user->toArray(),
            'created_at'         =>  \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at'         =>  \Illuminate\Support\Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        (new SystemUsersToken)->userTokenCreate($request);
        return new AuthInfoSystemUsersResource($request);
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
