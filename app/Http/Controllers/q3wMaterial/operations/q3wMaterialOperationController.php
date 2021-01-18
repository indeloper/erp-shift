<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Class q3wMaterialOperationController
 * @package App\Http\Controllers\q3wMaterial\operations
 */
class q3wMaterialOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('materials.operations.all');
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

    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request)
    {
        $options = json_decode($request['data']);

        $response = array(
            "data" => (new q3wMaterialOperation)
                ->dxLoadOptions($options)
                ->leftJoin('q3w_operation_route_stages', 'operation_route_stage_id', '=', 'q3w_operation_route_stages.id')
                ->addSelect('q3w_material_operations.*', 'q3w_operation_route_stages.name as operation_route_stage_name')
                ->withMaterialsSummary()
                ->get(),
            "totalCount" => (new q3wMaterialOperation)->dxLoadOptions($options)->count()
        );

        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function projectObjectActiveOperations(Request $request)
    {
        $projectObject = ProjectObject::findOrFail($request->projectObjectId);

        return q3wMaterialOperation::where(function ($query) use ($projectObject) {
            $query->where('source_project_object_id', $projectObject->id)
                ->orWhere('destination_project_object_id', $projectObject->id);
        })
            ->onlyActive()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'operation_route_id', 'operation_route_stage_id'])
            ->toJson(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param q3wMaterialStandard $q3wMaterialStandard
     * @return \Illuminate\Http\Response
     */
    public function edit(q3wMaterialStandard $q3wMaterialStandard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param q3wMaterialStandard $q3wMaterialStandard
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, q3wMaterialStandard $q3wMaterialStandard)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param q3wMaterialStandard $q3wMaterialStandard
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {

    }
}
