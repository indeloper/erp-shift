<?php

namespace App\Http\Controllers\q3wMaterial;

use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use http\Exception;
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
                                ->leftJoin('q3w_material_accounting_types as d', 'a.accounting_type', '=', 'd.id')
                                ->get(['a.id as id', 'a.name as name', 'b.value as measure_unit_value', 'd.value as accounting_type_value'])
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
     * @return \Illuminate\Http\JsonResponse
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
     * @param  \App\models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return string
     */
    public function show(q3wMaterialStandard $q3wMaterialStandard)
    {
/*        return q3wMaterialStandard::with('types')
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE);*/
        return DB::table('q3w_material_standards as a')
            ->leftJoin('q3w_material_types as b', 'a.material_type', '=', 'b.id')
            ->get(['a.*', 'b.accounting_type', 'b.measure_unit'])
            ->toJSON();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
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
     * @param  \App\models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return \Illuminate\Http\JsonResponse
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
     * @param  \App\models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->all()["key"];

            $materialStandard = q3wMaterialStandard::find($id);
            $materialStandard -> delete();

            return response()->json([
                'result' => 'ok'
            ], 200);
        }
        catch (Exception $e){
            return response()->json([
                'result' => 'error',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }
}
