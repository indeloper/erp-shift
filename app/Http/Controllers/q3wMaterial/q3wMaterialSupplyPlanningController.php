<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Http\Controllers\Controller;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialBrand;
use App\Models\q3wMaterial\q3wMaterialBrandsRelation;
use App\Models\q3wMaterial\q3wMaterialSupplyMaterial;
use App\Models\q3wMaterial\q3wMaterialSupplyPlanning;
use Illuminate\Http\Request;
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

    /**
     * Get materials for supply planning details.
     *
     * @param int $planningObjectId The ID of the planning object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the materials for supply planning.
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to access the material supply planning.
     */
    public function getMaterialsForSupplyPlanning($planningObjectId)
    {
        $this->authorize('material_supply_planning_access');

        $data = q3wMaterialSupplyPlanning::leftJoin('q3w_material_brand_types', 'q3w_material_supply_planning.brand_type_id', '=', 'q3w_material_brand_types.id')
            ->leftJoin('q3w_material_supply_materials', 'q3w_material_supply_planning.id', '=', 'q3w_material_supply_materials.supply_planning_id')
            ->leftJoin('q3w_material_standards', 'q3w_material_supply_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('project_objects', 'q3w_material_supply_materials.source_project_object_id', '=', 'project_objects.id')
            ->select([
                'project_objects.short_name',
                'q3w_material_brand_types.name as brand_type_name',
                'q3w_material_standards.name as standard_name',
                'q3w_material_supply_planning.*',
                'q3w_material_supply_materials.weight',
                DB::Raw("CONCAT(`q3w_material_brand_types`.`name`,' ', `q3w_material_supply_planning`.`quantity`, ' м.п') as `brand_with_quantity`")])
            ->where('planning_object_id', $planningObjectId)
            ->get();

        return response()->json($data);
    }

    public function getMaterialsForSupplyPlanningDetails(Request $request)
    {
        $loadOptions = $request->loadOptions;
        $projectObject = $request->projectObjectId;
        $brandId = $request->brandId;
        $quantity = $request->quantity;
        $detailType = $request->detailType;

        $brandTypeId = q3wMaterialBrand::find($brandId)->brand_type_id;
        $brandsWithSameType = q3wMaterialBrand::where('brand_type_id', $brandTypeId)->pluck('id')->toArray();

        return (new q3wMaterial())
            ->dxLoadOptions($loadOptions)
            ->leftJoin('q3w_material_standards', 'q3w_material_standards.id', '=', 'q3w_materials.standard_id')
            ->leftJoin('q3w_material_comments', 'q3w_material_comments.id', '=', 'q3w_materials.comment_id')
            ->leftJoin('q3w_material_supply_materials', 'q3w_materials.id', '=', 'q3w_material_supply_materials.material_id')
            ->whereBetween('q3w_materials.quantity', [$quantity - SUPPLY_PLANNING_QUANTITY_DELTA, $quantity + SUPPLY_PLANNING_QUANTITY_DELTA])
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
            ->whereIn('q3w_materials.standard_id', q3wMaterialBrandsRelation::whereIn('brand_id', $brandsWithSameType)->pluck('standard_id')->toArray())
            ->where('q3w_materials.amount', '>', 0)
            ->whereNull('q3w_material_supply_materials.deleted_at')
            ->orderBy('q3w_materials.quantity')
            ->orderBy('q3w_material_standards.name')
            ->orderBy('q3w_materials.amount')
            ->get([
                'q3w_materials.id',
                'q3w_materials.standard_id',
                'q3w_materials.project_object',
                'q3w_materials.amount',
                'q3w_materials.quantity',
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.weight',
                'q3w_material_comments.comment',
                'q3w_material_supply_materials.material_id as supply_material_id'

            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function getAvailableMaterialList(Request $request) {
        $filterData = json_decode($request->input('loadOptions'));

        if (!isset($filterData->userData->brand_type_id) || !isset($filterData->userData->quantity)) {
            return json_encode([]);
        }

        return q3wMaterial::leftJoin('q3w_material_standards', 'q3w_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_material_brands_relations', 'q3w_materials.standard_id', '=', 'q3w_material_brands_relations.standard_id')
            ->leftJoin('q3w_material_brands', 'q3w_material_brands_relations.brand_id', '=', 'q3w_material_brands.id')
            ->leftJoin('q3w_material_brand_types', 'q3w_material_brands.brand_type_id', '=', 'q3w_material_brand_types.id')
            ->leftJoin('project_objects', 'q3w_materials.project_object', '=', 'project_objects.id')
            /*->leftJoin('q3w_material_supply_materials', function ($join) {
                $join->on('q3w_materials.project_object', '=', 'q3w_material_supply_materials.source_project_object_id');
                $join->on('rooms.id', '=', 'bookings.room_type_id');
            })*/
            ->whereNotNull('brand_type_id')
            ->where('quantity', '<>', 0)
            ->where('amount', '<>', 0)
            ->where('brand_type_id', '=', $filterData->userData->brand_type_id)
            ->whereBetween('quantity', [$filterData->userData->quantity - SUPPLY_PLANNING_QUANTITY_DELTA, $filterData->userData->quantity + SUPPLY_PLANNING_QUANTITY_DELTA])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('q3w_standard_properties_relations')
                    ->whereRaw('q3w_materials.standard_id = q3w_standard_properties_relations.standard_id');
            })
            ->groupBy(['q3w_materials.project_object', 'q3w_material_standards.id'])
            ->select([
                'q3w_materials.project_object',
                'q3w_materials.standard_id',
                DB::raw('sum(`q3w_materials`.`amount`) as `summary_amount`'),
                'q3w_materials.quantity',
                'project_objects.short_name',
                'q3w_material_standards.name',
                DB::raw('0 as reserved_weight'),
                DB::raw('round(sum(`amount` * `quantity` * `q3w_material_standards`.`weight`), 3) as summary_weight')
            ])
            ->get()
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
        $data = json_decode($request->all()["data"]);

        DB::beginTransaction();

        $materialSupplyPlanningRow = new q3wMaterialSupplyPlanning([
                'planning_object_id' => $data->supply_object_id,
                'brand_type_id' => $data->brand_type_id,
                'quantity' => $data->quantity,
                'planned_project_weight' => $data->planned_weight
            ]
        );

        $materialSupplyPlanningRow->save();

        foreach ($data->materialsData as $supplyMaterialData) {

            $supplyMaterial = q3wMaterialSupplyMaterial::leftJoin('q3w_material_supply_planning', 'q3w_material_supply_materials.supply_planning_id', '=', 'q3w_material_supply_planning.id')
                ->where('q3w_material_supply_materials.supply_planning_id', '=', $materialSupplyPlanningRow->id)
                ->where('q3w_material_supply_planning.quantity', '=', $data->quantity)
                ->where('q3w_material_supply_materials.source_project_object_id', '=', $supplyMaterialData->key->project_object)
                ->where('q3w_material_supply_materials.standard_id', '=', $supplyMaterialData->key->standard_id)
                ->select('q3w_material_supply_materials.*')
                ->first();

            if (isset($supplyMaterial)) {
                $supplyMaterial->update([
                    'weight' => $supplyMaterialData->data->reserved_weight
                ]);
            } else {
                q3wMaterialSupplyMaterial::create([
                    'standard_id' => $supplyMaterialData->key->standard_id,
                    'supply_planning_id' => $materialSupplyPlanningRow->id,
                    'source_project_object_id' => $supplyMaterialData->key->project_object,
                    'weight' => $supplyMaterialData->data->reserved_weight,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'key' => $materialSupplyPlanningRow->id
        ], 201);
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

        unset($modifiedData['computed_weight']);
        unset($modifiedData['planned_project_weight']);

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
