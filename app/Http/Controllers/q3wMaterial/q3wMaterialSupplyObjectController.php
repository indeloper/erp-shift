<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Http\Controllers\Controller;
use App\Models\q3wMaterial\q3wMaterialSupplyObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class q3wMaterialSupplyObjectController extends Controller
{
    /**
     * Получение списка всех объектов.
     */
    public function list(): JsonResponse
    {
        $objects = Q3wMaterialSupplyObject::all();

        return response()->json($objects);
    }

    /**
     * Создание нового объекта.
     */
    public function store(Request $request): JsonResponse
    {
        $object = q3wMaterialSupplyObject::create([
            'name' => json_decode($request['data'])->name,
        ]);

        return response()->json($object, 201);
    }

    /**
     * Обновление информации об объекте.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $object = q3wMaterialSupplyObject::findOrFail($id);
        $object->update([
            'name' => json_decode($request['data'])->name,
        ]);

        return response()->json($object);
    }

    /**
     * Удаление объекта.
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        $object = Q3wMaterialSupplyObject::findOrFail($id);
        $object->delete();

        return response()->json(null, 204);
    }
}
