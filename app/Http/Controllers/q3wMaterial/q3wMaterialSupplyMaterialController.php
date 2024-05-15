<?php

namespace App\Http\Controllers\q3wMaterial;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialSupplyMaterial;
use Illuminate\Http\Request;

class q3wMaterialSupplyMaterialController extends Controller
{
    /**
     * Display a view of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */

    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);

        return (new q3wMaterialSupplyMaterial())
            ->dxLoadOptions($loadOptions)
            ->leftJoin('q3w_materials', 'q3w_material_supply_materials.material_id', '=', 'q3w_materials.id')
            ->leftJoin('q3w_material_standards', 'q3w_material_standards.id', '=', 'q3w_materials.standard_id')
            ->leftJoin('q3w_material_comments', 'q3w_material_comments.id', '=', 'q3w_materials.comment_id')
            ->get([
                'q3w_material_supply_materials.id',
                'q3w_material_supply_materials.amount',
                'q3w_material_supply_materials.material_id',
                'q3w_materials.standard_id',
                'q3w_materials.project_object',
                'q3w_materials.amount as source_material_amount',
                'q3w_materials.quantity',
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.weight',
                'q3w_material_comments.comment',
            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->all()['data'], JSON_OBJECT_AS_ARRAY);

        foreach ($data as $dataValue) {
            $sourceMaterial = q3wMaterial::findOrFail($dataValue['material_id']);

            $materialSupplyMaterial = q3wMaterialSupplyMaterial::where('supply_planning_id', '=', $dataValue['supply_planning_id'])
                ->where('material_id', '=', $dataValue['material_id'])
                ->first();
            if (! isset($materialSupplyMaterial)) {
                $materialSupplyMaterial = new q3wMaterialSupplyMaterial([
                    'supply_planning_id' => $dataValue['supply_planning_id'],
                    'material_id' => $dataValue['material_id'],
                    'amount' => $sourceMaterial->amount,
                ]
                );
                $materialSupplyMaterial->save();
            }
        }

        return response()->json([
            'result' => 'ok',
            'key' => $materialSupplyMaterial->id,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        $id = $request->all()['key'];
        $modifiedData = json_decode($request->all()['modifiedData'], JSON_OBJECT_AS_ARRAY);

        $materialSupplyPlanningRow = q3wMaterialSupplyMaterial::findOrFail($id);

        $materialSupplyPlanningRow->update($modifiedData);

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        $data = json_decode($request->all()['data'], JSON_OBJECT_AS_ARRAY);

        foreach ($data as $dataValue) {
            q3wMaterialSupplyMaterial::where('supply_planning_id', '=', $dataValue['supply_planning_id'])
                ->where('material_id', '=', $dataValue['material_id'])
                ->forceDelete();
        }

        return response()->json([
            'result' => 'ok',
        ], 200);
    }
}
