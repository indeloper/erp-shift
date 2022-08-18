<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialBrand;
use App\Models\q3wMaterial\q3wMaterialBrandsRelation;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialSupplyPlanning;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wStandardPropertiesRelations;
use http\Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

define('SUPPLY_PLANNING_QUANTITY_DELTA', 0.2);

class q3wMaterialSupplyPlanningController extends Controller
{
    /**
     * Display a view of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */
    public function index()
    {
        return view('materials.material-supply-planning');
    }

    public function getMaterialsForSupplyPlanningDetails(Request $request)
    {
        $loadOptions = $request->loadOptions;
        $projectObject = $request->projectObjectId;
        $brandId = $request->brandId;
        $quantity = $request->quantity;
        $detailType = $request->detailType;

        return (new q3wMaterial())
            ->dxLoadOptions($loadOptions)
            ->leftJoin('q3w_material_standards', 'q3w_material_standards.id', '=', 'q3w_materials.standard_id')
            ->leftJoin('q3w_material_comments', 'q3w_material_comments.id', '=', 'q3w_materials.comment_id')
            ->whereBetween('quantity', [$quantity - SUPPLY_PLANNING_QUANTITY_DELTA, $quantity + SUPPLY_PLANNING_QUANTITY_DELTA])

            ->where(function($query) use ($detailType, $projectObject) {
                switch ($detailType) {
                    case "otherRemains":
                        $query->where('project_object', '<>', $projectObject);
                        break;
                    case "selfRemains":
                        $query->where('project_object', '=', $projectObject);
                        break;
                }
            })
            ->whereIn('q3w_materials.standard_id', q3wMaterialBrandsRelation::where('brand_id', $brandId)->pluck('standard_id')->toArray())
            ->where('amount', '>', 0)
            ->orderBy('quantity')
            ->orderBy('q3w_material_standards.name')
            ->orderBy('amount')
            ->get([
                'q3w_materials.id',
                'q3w_materials.standard_id',
                'q3w_materials.project_object',
                'q3w_materials.amount',
                'q3w_materials.quantity',
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.weight',
                'q3w_material_comments.comment'

            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);

        return (new q3wMaterialSupplyPlanning())
            ->dxLoadOptions($loadOptions)
            ->join('q3w_material_brands_relations', 'q3w_material_supply_planning.brand_id', '=', 'q3w_material_brands_relations.brand_id')
            ->join('q3w_material_standards', 'q3w_material_brands_relations.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_standard_properties_relations', 'q3w_material_standards.id', '=', 'q3w_standard_properties_relations.standard_id')
            ->leftJoin('q3w_material_brands', 'q3w_material_supply_planning.brand_id', '=', 'q3w_material_brands.id')
            ->whereNull('q3w_standard_properties_relations.id')
            ->get([
                'q3w_material_supply_planning.id',
                'q3w_material_supply_planning.project_object_id',
                'q3w_material_supply_planning.brand_id',
                'q3w_material_brands.brand_type_id',
                'q3w_material_supply_planning.quantity',
                'q3w_material_supply_planning.amount',
                'weight as standard_weight',
                DB::Raw('IFNULL((select ROUND(sum(`amount` * `quantity` * `weight`), 3) as `remains_weight`
                                   from `q3w_materials`
                                            join `q3w_material_brands_relations`
                                                 on `q3w_material_brands_relations`.`standard_id` = `q3w_materials`.`standard_id`
                                            join `q3w_material_standards` on `q3w_materials`.`standard_id` = `q3w_material_standards`.`id`
                                   where `q3w_materials`.`project_object` = `q3w_material_supply_planning`.`project_object_id`
                                     and `q3w_material_brands_relations`.`brand_id` = `q3w_material_supply_planning`.`brand_id`
                                     and `q3w_materials`.`quantity` between `q3w_material_supply_planning`.`quantity` - ' . SUPPLY_PLANNING_QUANTITY_DELTA . ' and `q3w_material_supply_planning`.`quantity` + ' . SUPPLY_PLANNING_QUANTITY_DELTA . '),
                                  0) as `remains_weight`')
            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY);

        $materialSupplyPlanningRow = new q3wMaterialSupplyPlanning([
                'project_object_id' => $data['project_object_id'],
                'brand_id' => $data['brand_id'],
                'quantity' => $data['quantity'],
                'amount' => $data['amount']
            ]
        );

        $materialSupplyPlanningRow->save();

        return response()->json([
            'result' => 'ok',
            'key' => $materialSupplyPlanningRow->id
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->all()["key"];
        $modifiedData = json_decode($request->all()["modifiedData"], JSON_OBJECT_AS_ARRAY);

        $materialSupplyPlanningRow = q3wMaterialSupplyPlanning::findOrFail($id);

        $materialSupplyPlanningRow->update($modifiedData);

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->all()["key"];

        $materialSupplyPlanningRow = q3wMaterialSupplyPlanning::findOrFail($id);

        $materialSupplyPlanningRow->delete();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }
}
