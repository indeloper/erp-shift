<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Http\Controllers\Controller;
use App\Models\q3wMaterial\q3wMaterialType;
use http\Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class q3wMaterialTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */
    public function index(): View
    {
        return view('materials.material-type');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $materialType = new q3wMaterialType(json_decode($request->all()['data'], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/));
            $materialType->save();

            return response()->json([
                'result' => 'ok',
                'key' => $materialType->id,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'result' => 'error',
                'errors' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return string
     */
    public function show(Request $request)
    {

        $dxLoadOptions = json_decode($request['data'])->dxLoadOptions;

        $response = [
            'data' => (new q3wMaterialType)
                ->dxLoadOptions($dxLoadOptions)
                ->get(),
            'totalCount' => (new q3wMaterialType)->dxLoadOptions($dxLoadOptions)->count(),
        ];

        $response = [
            'data' => (new q3wMaterialType)
                ->dxLoadOptions($dxLoadOptions)
                ->get(),
            'totalCount' => (new q3wMaterialType)->dxLoadOptions($dxLoadOptions)->count(),
        ];

        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function byKey(Request $request)
    {

        $id = $request->all()['key'];

        return q3wMaterialType::findOrFail($id)->toJSON(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return void
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $id = $request->all()['key'];
            $modifiedData = json_decode($request->all()['modifiedData'], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

            $materialType = q3wMaterialType::findOrFail($id);

            $materialType->update($modifiedData);

            return response()->json([
                'result' => 'ok',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'result' => 'error',
                'errors' => $e->getMessage(),
            ], 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $id = $request->all()['key'];

            $materialType = q3wMaterialType::find($id);
            $materialType->delete();

            return response()->json([
                'result' => 'ok',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'result' => 'error',
                'errors' => $e->getMessage(),
            ], 400);
        }
    }
}
