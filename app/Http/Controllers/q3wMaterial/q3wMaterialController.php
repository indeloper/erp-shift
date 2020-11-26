<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\ProjectObject;
use App\models\q3wMaterial\q3wMaterial;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\models\q3wMaterial\q3wMaterialSnapshot;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class q3wMaterialController extends Controller
{
    /**
     * Display a view of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = ProjectObject::all('id')->first()->value('id');
        }

        return view('materials.materials')->with([
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => q3wMaterialStandard::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name', 'address')->toJson(JSON_UNESCAPED_UNICODE),
            'snapshots' => q3wMaterialSnapshot::where('project_object_id', '=', $projectObjectId)->get()->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjectId' => $projectObjectId
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource snapshot.
     *
     * @param Request $request
     * @return string
     */
    public function snapshot(Request $request)
    {
        $snapshotId = $request->snapshotId;

        return DB::table('q3w_material_snapshot_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->where('a.snapshot_id', '=', $snapshotId)
            ->get(['a.*',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value',
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`quantity` END AS `length_quantity`'),
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`amount` ELSE `a`.`quantity` END AS `computed_quantity`'),
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN (`a`.`amount` * `a`.`quantity` * `b`.`weight`) ELSE (`a`.`quantity` * `b`.`weight`) END AS `computed_weight`')])
            ->toJSON();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\models\q3wMaterial\q3wMaterial $q3wMaterial
     * @return string
     */
    public function show(Request $request)
    {
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = ProjectObject::all('id')->first()->value('id');
        }

        return DB::table('q3w_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->where('a.project_object', '=', $projectObjectId)
            ->get(['a.*',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value',
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`quantity` END AS `length_quantity`'),
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`amount` ELSE `a`.`quantity` END AS `computed_quantity`'),
                DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN (`a`.`amount` * `a`.`quantity` * `b`.`weight`) ELSE (`a`.`quantity` * `b`.`weight`) END AS `computed_weight`')])
            ->toJSON();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\models\q3wMaterial\q3wMaterial  $q3wMaterial
     * @return \Illuminate\Http\Response
     */
    public function edit(q3wMaterial $q3wMaterial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\models\q3wMaterial\q3wMaterial  $q3wMaterial
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, q3wMaterial $q3wMaterial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\models\q3wMaterial\q3wMaterial  $q3wMaterial
     * @return \Illuminate\Http\Response
     */
    public function destroy(q3wMaterial $q3wMaterial)
    {
        //
    }
}
