<?php

namespace Svr\Core\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Svr\Core\Models\SystemModules;

class ApiModulesController extends Controller
{
    /**
     * Создание новой записи.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $model = new SystemModules();
        $record = $model->moduleCreate($request);
        return response()->json($record, 201);
    }

    /**
     * Обновление существующей записи.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
 */
    public function update(Request $request): JsonResponse
    {
        $record = new SystemModules();
        $request->setMethod('PUT');
        $record->moduleUpdate($request);
        return response()->json($record);
    }

    /**
     * Получение списка записей с пагинацией.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
 */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15); // Количество записей на странице по умолчанию
        $records = SystemModules::paginate($perPage);
        return response()->json($records);
    }
}