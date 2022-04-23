<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMeasureUnit;
use http\Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class q3wMaterialStandardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('materials.material-standard')->with([
            'measureUnits' => q3wMeasureUnit::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => DB::table('q3w_material_types as a')
                ->leftJoin('q3w_measure_units as b', 'a.measure_unit', '=', 'b.id')
                ->get(['a.id as id', 'a.name as name', 'b.value as measure_unit_value'])
                                ->toJson(JSON_UNESCAPED_UNICODE)
        ]);


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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $materialStandard = new q3wMaterialStandard(json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/));
            $materialStandard->save();

            return response()->json([
                'result' => 'ok',
                'key' => $materialStandard->id
            ], 200);
        }
        catch(Exception $e)
        {
            return response()->json([
                'result' => 'error',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request): string
    {
        $options = json_decode($request['data']);

        return (new q3wMaterialStandard())
            ->dxLoadOptions($options)
            ->leftJoin('q3w_material_types as b', 'q3w_material_standards.material_type', '=', 'b.id')
            ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
            ->select(['q3w_material_standards.*', 'b.measure_unit', 'd.value as measure_unit_value'])
            ->get()
            ->toJSON();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return \Illuminate\Http\Response
     */
    public function edit(q3wMaterialStandard $q3wMaterialStandard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return JsonResponse
     */
    public function update(Request $request, q3wMaterialStandard $q3wMaterialStandard)
    {
        try {
            $id = $request->all()["key"];
            $modifiedData = json_decode($request->all()["modifiedData"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

            $materialStandard = q3wMaterialStandard::findOrFail($id);

            $materialStandard -> update($modifiedData);

            return response()->json([
                'result' => 'ok'
            ], 200);
        }
        catch(Exception $e)
        {
            return response()->json([
                'result' => 'error',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $id = $request->all()["key"];

            $materialStandard = q3wMaterialStandard::find($id);
            $materialStandard->delete();

            return response()->json([
                'result' => 'ok'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'result' => 'error',
                'errors' => $e->getMessage(),
            ], 400);
        }
    }

    public function list(Request $request): string
    {
        $dxLoadOptions = json_decode($request['data'])->dxLoadOptions;

        return (new q3wMaterialStandard())->dxLoadOptions($dxLoadOptions)
            ->leftJoin('q3w_material_types as b', 'q3w_material_standards.material_type', '=', 'b.id')
            ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
            ->orderBy('selection_counter', 'desc')
            ->orderBy('q3w_material_standards.name')
            ->get(['q3w_material_standards.id',
                'q3w_material_standards.id as standard_id',
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.weight',
                'q3w_material_standards.material_type',
                'q3w_material_standards.participates_in_search',
                'q3w_material_standards.name',
                'q3w_material_standards.selection_counter',
                'b.name as material_type_name',
                'b.measure_unit',
                'b.accounting_type',
                'd.value as measure_unit_value'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function incriminateSelectionCounter(Request $request): JsonResponse {
        $requestData = json_decode($request->getContent(), false);
        $standardId = $requestData->standardId;
        $standard = q3wMaterialStandard::findOrFail($standardId);
        $standard->selection_counter += 1;
        $standard->save();

        return response()->json([
            'result' => 'ok',
            'value' => $standard->selection_counter
        ], 200);
    }
}
