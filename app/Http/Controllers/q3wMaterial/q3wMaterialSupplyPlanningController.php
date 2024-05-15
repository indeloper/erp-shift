<?php

namespace App\Http\Controllers\q3wMaterial;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
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
    public function index(): View
    {
        return view('materials.material-supply-planning');
    }

    /**
     * Get materials for supply planning details.
     *
     * @param  int  $planningObjectId  The ID of the planning object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the materials for supply planning.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException If the user is not authorized to access the material supply planning.
     */
    public function getMaterialsForSupplyPlanning(int $planningObjectId): JsonResponse
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
                'q3w_material_supply_materials.id as supply_material_id',
                'q3w_material_supply_materials.weight',
                DB::Raw("CONCAT(`q3w_material_brand_types`.`name`,' ', `q3w_material_supply_planning`.`quantity`, ' м.п') as `brand_with_quantity`")])
            ->where('planning_object_id', $planningObjectId)
            ->get();

        return response()->json($data);
    }

    public function getSummary(Request $request)
    {
        return q3wMaterialSupplyMaterial::leftJoin('q3w_material_supply_planning', 'q3w_material_supply_materials.supply_planning_id', '=', 'q3w_material_supply_planning.id')
            ->leftJoin('project_objects', 'q3w_material_supply_materials.source_project_object_id', '=', 'project_objects.id')
            ->leftJoin('q3w_material_supply_objects', 'q3w_material_supply_planning.planning_object_id', '=', 'q3w_material_supply_objects.id')
            ->leftJoin('q3w_material_standards', 'q3w_material_supply_materials.standard_id', '=', 'q3w_material_standards.id')
            ->select([
                'q3w_material_supply_objects.name as supply_object_name',
                'project_objects.short_name as project_object_name',
                'q3w_material_standards.name as standard_name',
                'q3w_material_supply_planning.quantity',
                'q3w_material_supply_materials.weight',
            ])
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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
            ->where(function ($query) use ($detailType, $projectObject) {
                switch ($detailType) {
                    case 'otherRemains':
                        $query->where('project_object', '<>', $projectObject);
                        break;
                    case 'selfRemains':
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
                'q3w_material_supply_materials.material_id as supply_material_id',

            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function getAvailableMaterialList(Request $request)
    {
        $filterData = json_decode($request->input('loadOptions'));

        if (! isset($filterData->userData->brand_type_id) || ! isset($filterData->userData->quantity)) {
            return json_encode([]);
        }

        return q3wMaterial::leftJoin('q3w_material_standards', 'q3w_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_material_brands_relations', 'q3w_materials.standard_id', '=', 'q3w_material_brands_relations.standard_id')
            ->leftJoin('q3w_material_brands', 'q3w_material_brands_relations.brand_id', '=', 'q3w_material_brands.id')
            ->leftJoin('q3w_material_brand_types', 'q3w_material_brands.brand_type_id', '=', 'q3w_material_brand_types.id')
            ->leftJoin('project_objects', 'q3w_materials.project_object', '=', 'project_objects.id')
            ->leftJoin(DB::raw('(select q3w_material_supply_materials.id, q3w_material_supply_materials.standard_id, source_project_object_id, q3w_material_supply_planning.planning_object_id, q3w_material_supply_materials.weight, q3w_material_supply_planning.quantity as supply_quantity
                        from `q3w_material_supply_materials`
                                 left join `q3w_material_supply_planning`
                                           on `q3w_material_supply_materials`.`supply_planning_id` = `q3w_material_supply_planning`.`id`) as supply_materials'),
                function ($join) use ($filterData) {
                    $join->on('supply_materials.standard_id', 'q3w_materials.standard_id');
                    $join->on('supply_materials.source_project_object_id', 'project_objects.id');
                    $join->on('supply_materials.planning_object_id', DB::raw($filterData->userData->planning_object_id));
                    $join->on('supply_materials.supply_quantity', DB::raw($filterData->userData->quantity));
                })
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
                DB::raw('supply_materials.weight as reserved_weight'),
                DB::raw('IFNULL(supply_materials.id, UUID()) as reserved_id'),
                DB::raw('round(sum(`amount` * `quantity` * `q3w_material_standards`.`weight`), 3) as summary_weight'),
                DB::raw('(select round(sum(tmp_supply_materials.weight), 3) '.
                    'from q3w_material_supply_materials as tmp_supply_materials '.
                    'left join q3w_material_supply_planning as tmp_supply_planning '.
                    'on tmp_supply_materials.supply_planning_id = tmp_supply_planning.id '.
                    'where tmp_supply_planning.planning_object_id <> '.$filterData->userData->planning_object_id.' '.
                    'and tmp_supply_planning.quantity between '.($filterData->userData->quantity - SUPPLY_PLANNING_QUANTITY_DELTA).' and '.($filterData->userData->quantity + SUPPLY_PLANNING_QUANTITY_DELTA).' '.
                    'and tmp_supply_materials.standard_id = q3w_materials.standard_id '.
                    'and tmp_supply_materials.source_project_object_id = q3w_materials.project_object) as reserved_weight_on_other_objects'),
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
                                     and `q3w_materials`.`quantity` between `q3w_material_supply_planning`.`quantity` - '.SUPPLY_PLANNING_QUANTITY_DELTA.' and `q3w_material_supply_planning`.`quantity` + '.SUPPLY_PLANNING_QUANTITY_DELTA.'),
                                  0) as `remains_weight`'),
            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->all()['data']);

        DB::beginTransaction();

        $materialSupplyPlanningRow = new q3wMaterialSupplyPlanning([
            'planning_object_id' => $data->supply_object_id,
            'brand_type_id' => $data->brand_type_id,
            'quantity' => $data->quantity,
            'planned_project_weight' => $data->planned_project_weight,
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
                    'weight' => $supplyMaterialData->data->reserved_weight,
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
            'key' => $materialSupplyPlanningRow->id,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        $id = $request->all()['key']['id'];
        $modifiedData = json_decode($request->all()['modifiedData']);

        DB::beginTransaction();

        $materialSupplyPlanningRow = q3wMaterialSupplyPlanning::findOrFail($id);

        if (isset($modifiedData->planned_project_weight)) {
            $materialSupplyPlanningRow->update(
                [
                    'planned_project_weight' => $modifiedData->planned_project_weight,
                ]
            );

            $materialSupplyPlanningRow->save();
        }

        foreach ($modifiedData->materialsData as $supplyMaterialData) {
            $supplyMaterial = q3wMaterialSupplyMaterial::find($supplyMaterialData->key->reserved_id);

            if (isset($supplyMaterial)) {
                $supplyMaterial->update([
                    'weight' => $supplyMaterialData->data->reserved_weight,
                ]);
            } else {
                $supplyMaterial = q3wMaterialSupplyMaterial::create([
                    'standard_id' => $supplyMaterialData->key->standard_id,
                    'supply_planning_id' => $materialSupplyPlanningRow->id,
                    'source_project_object_id' => $supplyMaterialData->key->project_object,
                    'weight' => $supplyMaterialData->data->reserved_weight,
                ]);
            }

            $supplyMaterial->save();
        }

        DB::commit();

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->all()['key']['id'];

        q3wMaterialSupplyMaterial::where('supply_planning_id', '=', $id)->delete();
        q3wMaterialSupplyPlanning::findOrFail($id)->delete();

        return response()->json([
            'result' => 'ok',
        ], 200);
    }
}
