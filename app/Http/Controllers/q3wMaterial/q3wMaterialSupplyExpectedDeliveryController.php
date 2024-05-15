<?php

namespace App\Http\Controllers\q3wMaterial;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\q3wMaterial\q3wMaterialSupplyExpectedDeliveries;
use Illuminate\Http\Request;

define('SUPPLY_PLANNING_QUANTITY_DELTA', 0.2);

class q3wMaterialSupplyExpectedDeliveryController extends Controller
{
    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = $request->loadOptions;
        $materialSupplyPlanningId = $request->materialSupplyPlanningId;

        return (new q3wMaterialSupplyExpectedDeliveries())
            ->dxLoadOptions($loadOptions)
            ->leftJoin('q3w_material_supply_planning', 'supply_planning_id', '=', 'q3w_material_supply_planning.id')
            ->join('q3w_material_brands_relations', 'q3w_material_supply_planning.brand_id', '=', 'q3w_material_brands_relations.brand_id')
            ->join('q3w_material_standards', 'q3w_material_brands_relations.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_standard_properties_relations', 'q3w_material_standards.id', '=', 'q3w_standard_properties_relations.standard_id')
            ->leftJoin('q3w_material_brands', 'q3w_material_supply_planning.brand_id', '=', 'q3w_material_brands.id')
            ->whereNull('q3w_standard_properties_relations.id')
            ->where('q3w_material_supply_planning.id', '=', $materialSupplyPlanningId)
            ->get([
                'q3w_material_supply_expected_deliveries.id',
                'q3w_material_supply_expected_deliveries.contractor_id',
                'q3w_material_supply_expected_deliveries.quantity',
                'q3w_material_supply_expected_deliveries.amount',
                'weight as standard_weight',
            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->all()['data'], JSON_OBJECT_AS_ARRAY);

        $materialSupplyPlanningRow = new q3wMaterialSupplyExpectedDeliveries([
            'supply_planning_id' => $data['supply_planning_id'],
            'contractor_id' => $data['contractor_id'],
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
        ]
        );

        $materialSupplyPlanningRow->save();

        return response()->json([
            'result' => 'ok',
            'key' => $materialSupplyPlanningRow->id,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        $id = $request->all()['key'];
        $modifiedData = json_decode($request->all()['modifiedData'], JSON_OBJECT_AS_ARRAY);

        $materialSupplyPlanningRow = q3wMaterialSupplyExpectedDeliveries::findOrFail($id);

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
        $id = $request->all()['key'];

        $materialSupplyPlanningRow = q3wMaterialSupplyExpectedDeliveries::findOrFail($id);

        $materialSupplyPlanningRow->delete();

        return response()->json([
            'result' => 'ok',
        ], 200);
    }
}
