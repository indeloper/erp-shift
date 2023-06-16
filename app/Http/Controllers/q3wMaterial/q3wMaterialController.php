<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialComment;
use App\Models\q3wMaterial\q3wMaterialSnapshotMaterial;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\UsersSetting;
use App\Services\q3wMaterialAccounting\Reports\MaterialRemainsXLSXReport;
use App\Services\q3wMaterialAccounting\Reports\MaterialObjectsRemainsXLSXReport;
use App\Services\q3wMaterialAccounting\Reports\MaterialTableXLSXReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $projectObjectId = (new UsersSetting)->getSetting('material_accounting_last_project_object_id');
        if (!isset($projectObjectId)) {
            $projectObjectId = $request->project_object ?? ProjectObject::whereNotNull('short_name')
                    ->where('is_participates_in_material_accounting', '=', 1)
                    ->orderBy("short_name")
                    ->get(['id'])
                    ->first()->id;
        }
        return view('materials.materials')->with([
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => q3wMaterialStandard::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjects' => ProjectObject::where('is_participates_in_material_accounting', '=', 1)
                ->whereNotNull('short_name')
                ->orderBy('short_name')
                ->get('id', 'name', 'short_name', 'address')
                ->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjectId' => $projectObjectId
        ]);
    }

    public function table(Request $request)
    {
        $projectObjectId = $request->project_object ?? ProjectObject::whereNotNull('short_name')
                ->orderBy("short_name")
                ->get(['id'])
                ->first()->id;

        return view('materials.material-table')->with([
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => q3wMaterialStandard::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name', 'address')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjectId' => $projectObjectId
        ]);
    }

    public function remains(Request $request)
    {
        $projectObjectId = $request->projectObjectId ?? ProjectObject::whereNotNull('short_name')
                ->orderBy("short_name")
                ->get(['id'])
                ->first()->id;

        if (isset($request->requestedDate)) {
            $requestedDate = $request->requestedDate;
        } else {
            $requestedDate = Carbon::today()->format('Y-m-d');
        }
        return view('materials.material-remains')->with([
            'projectObjectId' => $projectObjectId,
            'requestedDate' => $requestedDate
        ]);
    }

    public function objectsRemains(Request $request)
    {
        $projectObjectId = $request->projectObjectId ?? ProjectObject::whereNotNull('short_name')
                ->orderBy("short_name")
                ->get(['id'])
                ->first()->id;

        if (isset($request->requestedDate)) {
            $requestedDate = $request->requestedDate;
        } else {
            $requestedDate = Carbon::today()->format('Y-m-d');
        }
        return view('materials.material-objects-remains')->with([
            'projectObjectId' => $projectObjectId,
            'requestedDate' => $requestedDate
        ]);
    }
    

    /**
     * @param Request $request
     * @param int $projectObjectId
     * @return mixed
     */
    public function snapshotList(Request $request)
    {
        $projectObjectId = $request["projectObjectId"];
        return q3wMaterialOperation::join('q3w_material_snapshots', 'q3w_material_snapshots.operation_id', 'q3w_material_operations.id')
            ->where('q3w_material_snapshots.project_object_id', '=', $projectObjectId)
            ->orderBy('q3w_material_snapshots.created_at', 'desc')
            ->get(['q3w_material_operations.id',
                'q3w_material_operations.operation_route_stage_id',
                'q3w_material_snapshots.created_at',
                'q3w_material_operations.operation_route_id',
                'source_project_object_id',
                'destination_project_object_id'])
            ->toJson(JSON_UNESCAPED_UNICODE);
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
            ->where('amount', '<>', 0)
            ->where('quantity', '<>', 0)
            ->get(['a.*',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value'])
            ->toJSON();
    }

    public function actualProjectObjectMaterialsList(Request $request)
    {
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;
        }

        if (isset($request->operationId)) {
            $operationId = $request->operationId;
        } else {
            $operationId = 0;
        }

        $activeOperationMaterials = DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f', 'a.material_operation_id', '=', 'f.id')
            ->leftJoin('q3w_operation_material_comments as g', 'a.comment_id', '=', 'g.id')
            ->where('f.source_project_object_id', $projectObjectId)
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
            ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
            ->where('a.material_operation_id', '<>', $operationId)
            ->get(['a.id',
                'a.standard_id',
                'a.quantity',
                'a.amount',
                'a.comment_id',
                'g.comment',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value'])
            ->toArray();

        $materials = DB::table('q3w_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_comments as f', 'a.comment_id', '=', 'f.id')
            ->leftJoin('q3w_material_brands_relations as g', 'a.standard_id', '=', 'g.standard_id')
            ->where('a.project_object', '=', $projectObjectId)
            ->where('amount', '<>', 0)
            ->where('quantity', '<>', 0)
            ->groupBy(['a.id'])
            ->orderBy('selection_counter', 'desc')
            ->orderBy('b.name')
            ->get(['a.*',
                'f.comment',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',
                DB::Raw("GROUP_CONCAT(DISTINCT `g`.`brand_id` SEPARATOR ',') as standard_brands")])
            ->toArray();

        foreach ($activeOperationMaterials as $operationMaterial) {
            foreach ($materials as $material) {
                switch ($operationMaterial->accounting_type) {
                    case 2:
                        if (($operationMaterial->standard_id == $material->standard_id)
                            and ($operationMaterial->quantity == $material->quantity)
                            and ($operationMaterial->comment == $material->comment)) {
                            $material->amount -= $operationMaterial->amount;
                            if ($material->amount <= 0) {
                                unset($material);
                            }
                        }
                        break;
                    default:
                        if (($operationMaterial->standard_id == $material->standard_id)
                            and ($operationMaterial->comment == $material->comment)) {
                            $material->quantity -= $operationMaterial->quantity * $operationMaterial->amount;
                            if ($material->quantity <= 0) {
                                unset($material);
                            }
                        }
                }
            }
        }

        return json_encode($materials, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function allProjectObjectMaterialsWithActualAmountList(Request $request)
    {
        $projectObjectId = $request->project_object ?? ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;

        return DB::table('q3w_material_standards as a')
            ->leftJoin('q3w_materials as b', function ($join) use ($projectObjectId) {
                $join->on('a.id', '=', 'b.standard_id');
                $join->on('b.project_object', '=', DB::RAW($projectObjectId));
            })
            ->leftJoin('q3w_material_types as d', 'a.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_comments as f', 'b.comment_id', '=', 'f.id')
            ->orderBy('a.selection_counter', 'desc')
            ->orderBy('a.name')
            ->get([DB::Raw('UUID() as `id`'),
                'a.id as standard_id',
                'a.name as standard_name',
                'b.amount',
                'b.quantity',
                'a.material_type',
                'a.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',
                'b.comment_id as initial_comment_id',
                'b.comment_id as comment_id',
                'f.comment as initial_comment',
                'f.comment as comment',
                DB::RAW('0 as from_operation')])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\q3wMaterial\q3wMaterial $q3wMaterial
     * @return string
     */
    public function show(Request $request)
    {
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
            (new UsersSetting)->setSetting('material_accounting_last_project_object_id', $projectObjectId);
        } else {
            $projectObjectId = ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;
        }

        $activeOperationMaterials = DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f', 'a.material_operation_id', '=', 'f.id')
            ->where(function ($query) use ($projectObjectId) {
                $query->where('f.source_project_object_id', $projectObjectId)
                    ->orWhere('f.destination_project_object_id', $projectObjectId);
            })
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
            ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
            ->get(['a.id',
                'a.standard_id',
                'a.quantity',
                'a.amount',
                'a.initial_comment_id',
                DB::RAW('IF (f.source_project_object_id = ' . $projectObjectId . ', -1, 1) as amount_modifier'),
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',
                DB::RAW('1 as from_operation')])
            ->toArray();

        $materials = DB::table('q3w_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_comments as f', 'a.comment_id', '=', 'f.id')
            ->where('a.project_object', '=', $projectObjectId)
            ->where('amount', '<>', 0)
            ->where('quantity', '<>', 0)
            ->get(['a.*',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',
                'f.comment',
                DB::RAW('0 as from_operation')])
            ->toArray();

        foreach ($activeOperationMaterials as $operationMaterial) {
            foreach ($materials as $material) {
                switch ($operationMaterial->accounting_type) {
                    case 2:
                        if (($operationMaterial->standard_id == $material->standard_id)
                            and ($operationMaterial->quantity == $material->quantity)
                            and $operationMaterial->initial_comment_id == $material->comment_id) {
                            if ($operationMaterial->amount_modifier < 0) {
                                $material->amount += $operationMaterial->amount * $operationMaterial->amount_modifier;
                            }
                        }
                        break;
                    default:
                        if (($operationMaterial->standard_id == $material->standard_id)
                            and ($operationMaterial->initial_comment_id == $material->comment_id)) {
                            if ($operationMaterial->amount_modifier < 0) {
                                $material->quantity += $operationMaterial->quantity * $operationMaterial->amount * $operationMaterial->amount_modifier;
                            }
                        }
                }
            }
        }

        return json_encode($materials, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function standardHistoryList(Request $request)
    {
        $materialStandard = q3wMaterialStandard::findOrFail($request->materialStandardId);
        $materialType = q3wMaterialType::findOrFail($materialStandard->material_type);
        $materialQuantity = $request->materialQuantity;
        $projectObject = ProjectObject::findOrFail($request->projectObjectId);
        $commentId = $request->commentId;


        return q3wMaterialOperation::rightJoin('q3w_operation_materials as a', 'a.material_operation_id', '=', 'q3w_material_operations.id')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_operation_material_comments as f', 'comment_id', '=', 'f.id')
            ->leftJoin('project_objects as g', 'q3w_material_operations.source_project_object_id', '=', 'g.id')
            ->leftJoin('project_objects as h', 'q3w_material_operations.destination_project_object_id', '=', 'h.id')
            ->leftJoin('contractors as i', 'q3w_material_operations.contractor_id', '=', 'i.id')
            ->leftJoin('q3w_operation_routes as j', 'q3w_material_operations.operation_route_id', '=', 'j.id')
            ->leftJoin('q3w_material_transformation_types as k', 'q3w_material_operations.transformation_type_id', '=', 'k.id')
            ->where(function ($query) use ($materialStandard, $materialType, $materialQuantity, $commentId) {
                if (isset($commentId)) {//If comment Id passed we need to check material's comment field
                    $comment = q3wMaterialComment::findOrFail($commentId)->comment;
                    $query->where('f.comment', '=', $comment);
                } else {
                    $query->whereNull('comment_id');
                }

                switch ($materialType->accounting_type) {
                    case 2:
                        $query->where('standard_id', $materialStandard->id)
                            ->where('quantity', $materialQuantity);
                        break;
                    default:
                        $query->where('standard_id', $materialStandard->id);
                }
            })
            ->where(function ($query) use ($projectObject) {
                $query->where('q3w_material_operations.source_project_object_id', $projectObject->id)
                    ->orWhere('q3w_material_operations.destination_project_object_id', $projectObject->id);
            })
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
            ->whereIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->orderBy('q3w_material_operations.created_at', 'desc')
            ->get(['a.amount',
                'a.quantity',
                'a.transform_operation_stage_id',
                'b.weight',
                'q3w_material_operations.id as id',
                'q3w_material_operations.operation_route_id',
                'q3w_material_operations.operation_route_stage_id',
                'q3w_material_operations.source_project_object_id',
                'q3w_material_operations.destination_project_object_id',
                'q3w_material_operations.created_at as operation_date',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',
                'g.short_name as source_project_object_name',
                'h.short_name as destination_project_object_name',
                'i.short_name as contractor_short_name',
                'j.name as route_name',
                'k.value as transformation_type_value'
            ])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function reservedMaterialsList(Request $request)
    {
        $projectObjectId = $request->project_object ?? ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;

        return DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f', 'a.material_operation_id', '=', 'f.id')
            ->leftJoin('q3w_material_comments as g', 'a.initial_comment_id', '=', 'g.id')
            ->where('f.source_project_object_id', $projectObjectId)
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
            ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
            ->get(['a.id',
                'a.standard_id',
                'a.quantity',
                'a.amount',
                'g.comment',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',
                DB::RAW('1 as from_operation')])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function getMaterialTableQuery($projectObjectId, $filterOptions)
    {
        return (new q3wMaterialOperation)
            ->dxLoadOptions($filterOptions, true)
            ->leftJoin('q3w_operation_materials', 'q3w_operation_materials.material_operation_id', '=', 'q3w_material_operations.id')
            ->leftJoin('q3w_material_standards', 'q3w_operation_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('project_objects AS source_project_objects', 'q3w_material_operations.source_project_object_id', '=', 'source_project_objects.id')
            ->leftJoin('project_objects AS destination_project_objects', 'q3w_material_operations.destination_project_object_id', '=', 'destination_project_objects.id')
            ->leftJoin('q3w_operation_material_comments', 'q3w_operation_materials.comment_id', '=', 'q3w_operation_material_comments.id')
            ->leftJoin('contractors', 'q3w_material_operations.contractor_id', '=', 'contractors.id')
            ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', '=', 'q3w_material_types.id')
            ->leftJoin('q3w_measure_units', 'q3w_material_types.measure_unit', '=', 'q3w_measure_units.id')
            ->leftJoin('q3w_operation_routes', 'q3w_material_operations.operation_route_id', '=', 'q3w_operation_routes.id')
            ->leftJoin('q3w_material_transformation_types', 'q3w_material_operations.transformation_type_id', '=', 'q3w_material_transformation_types.id')
            ->whereIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->where('amount', '<>', '0')
            ->where(function ($query) use ($projectObjectId) {
                $query->where('q3w_material_operations.source_project_object_id', '=', $projectObjectId)
                    ->orWhere('q3w_material_operations.destination_project_object_id', '=', $projectObjectId);
            })
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)")
            ->orderBy('operation_date')
            ->orderBy('q3w_material_operations.id')
            ->orderBy('q3w_operation_materials.transform_operation_stage_id')
            ->orderBy('q3w_material_standards.name')
            ->orderBy('q3w_operation_material_comments.comment')
            ->orderBy('quantity')
            ->orderBy('amount')
            ->select(['q3w_material_operations.id',
                'q3w_material_standards.id as standard_id',
                DB::Raw('DATE(q3w_material_operations.operation_date) as operation_date'),
                'q3w_material_operations.operation_route_id',
                'q3w_material_operations.operation_route_stage_id',
                'q3w_material_operations.source_responsible_user_id',
                'q3w_material_operations.destination_responsible_user_id',
                'q3w_material_operations.source_project_object_id',
                'q3w_material_operations.destination_project_object_id',
                'q3w_material_operations.transformation_type_id',
                'q3w_material_transformation_types.value as transformation_type_value',
                'q3w_operation_materials.transform_operation_stage_id',
                DB::Raw('IF (`q3w_material_operations`.`operation_route_id` = 3, `q3w_material_transformation_types`.`value`, `q3w_operation_routes`.`name`) as route_name'),
                'q3w_material_standards.name as standard_name',
                'q3w_operation_materials.quantity',
                'q3w_operation_materials.amount',
                'q3w_material_standards.weight as standard_weight',
                'q3w_measure_units.value as measure_unit_value',
                DB::Raw('ROUND(`q3w_operation_materials`.`quantity` * `q3w_operation_materials`.`amount`, 2) AS `total_quantity`'),
                DB::Raw('ROUND(`q3w_operation_materials`.`quantity` * `q3w_operation_materials`.`amount` * q3w_material_standards.weight, 3) AS `weight`'),
                DB::Raw('CASE WHEN `q3w_material_operations`.`operation_route_id` = 1 THEN `contractors`.`short_name`
                          WHEN `q3w_material_operations`.`operation_route_id` = 2 THEN IF(`destination_project_object_id` = ' . $projectObjectId . ', `source_project_objects`.`short_name`, NULL)
                        END AS `coming_from_project_object`'),
                DB::Raw('IF(`source_project_object_id` = ' . $projectObjectId . ', `destination_project_objects`.`short_name`, NULL) AS `outgoing_to_project_object`'),
                'q3w_operation_material_comments.comment',
                DB::Raw('IF(`q3w_material_operations`.`operation_route_id` = 1, `q3w_material_operations`.`consignment_note_number`, NULL) AS `item_transport_consignment_note_number`'),
                DB::Raw('IF(`q3w_material_operations`.`operation_route_id` = 2, `q3w_material_operations`.`consignment_note_number`, NULL) AS `consignment_note_number`')
            ]);
    }

    public function materialsTableList(Request $request): string
    {
        $options = json_decode($request['data']);
        $projectObjectId = json_decode($request["projectObjectId"]);

        $materialsList = $this->getMaterialTableQuery($projectObjectId, $options)
            ->get();

        unset($options->skip);
        unset($options->take);
        $totalCount = $this->getMaterialTableQuery($projectObjectId, $options)
            ->get()
            ->count();

        return json_encode(array(
            "data" => $materialsList,
            "totalCount" => $totalCount
        ),
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function printMaterialsTable(Request $request)
    {
        $filterText = json_decode($request->input('filterList'));
        $options = json_decode($request['filterOptions']);
        $projectObjectId = json_decode($request["projectObjectId"]);

        $materialsList = $this->getMaterialTableQuery($projectObjectId, $options)
            ->get()
            ->toArray();

        return (new MaterialTableXLSXReport($projectObjectId, $materialsList, $filterText, null))->export();
    }

    public function getMaterialRemainsQuery($projectObjectId, $date, $filterOptions)
    {
        return (new q3wMaterialStandard)
            ->dxLoadOptions($filterOptions, true)
            ->leftJoin(DB::Raw("(SELECT
                                          `source_project_object_id`,
                                          `destination_project_object_id`,
                                          `standard_id`,
                                          SUM(`amount`) AS `coming_to_material_amount`,
                                          SUM(`amount` * `quantity`) AS `coming_to_material_quantity`
                                        FROM `q3w_operation_materials`
                                          LEFT JOIN `q3w_material_operations`
                                            ON `q3w_operation_materials`.`material_operation_id` = `q3w_material_operations`.`id`
                                          LEFT JOIN `q3w_material_standards`
                                            ON `q3w_operation_materials`.`standard_id` = `q3w_material_standards`.`id`
                                          LEFT JOIN `q3w_operation_route_stages`
                                            ON `q3w_material_operations`.`operation_route_stage_id` = `q3w_operation_route_stages`.`id`
                                        WHERE ((`q3w_material_operations`.`operation_route_id` IN (1, 2)
                                            AND `destination_project_object_id` = '$projectObjectId')
                                           OR (`q3w_material_operations`.`operation_route_id` IN (3) and
                                               `source_project_object_id` = '$projectObjectId' and
                                               `q3w_operation_materials`.`transform_operation_stage_id` in (2, 3)))
                                        AND DATE(`operation_date`) <= DATE('$date')
                                        AND NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)
                                        AND `q3w_operation_route_stages`.`operation_route_stage_type_id` = 2
                                        GROUP BY
                                                 `standard_id`) AS `coming_to_materials`"), 'coming_to_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin(DB::Raw("(SELECT
                                          `source_project_object_id`,
                                          `destination_project_object_id`,
                                          `standard_id`,
                                          SUM(`amount`) AS `outgoing_material_amount`,
                                          SUM(`amount` * `quantity`) AS `outgoing_material_quantity`
                                        FROM `q3w_operation_materials`
                                          LEFT JOIN `q3w_material_operations`
                                            ON `q3w_operation_materials`.`material_operation_id` = `q3w_material_operations`.`id`
                                          LEFT JOIN `q3w_material_standards`
                                            ON `q3w_operation_materials`.`standard_id` = `q3w_material_standards`.`id`
                                          LEFT JOIN `q3w_operation_route_stages`
                                            ON `q3w_material_operations`.`operation_route_stage_id` = `q3w_operation_route_stages`.`id`
                                        WHERE ((`q3w_material_operations`.`operation_route_id` IN (2, 4)
                                            AND `source_project_object_id` = '$projectObjectId')
                                           OR (`q3w_material_operations`.`operation_route_id` IN (3) and
                                               `source_project_object_id` = '$projectObjectId' and
                                               `q3w_operation_materials`.`transform_operation_stage_id` in (1, 4)))
                                        AND DATE(`operation_date`) <= DATE('$date')
                                        AND `q3w_operation_route_stages`.`operation_route_stage_type_id` = 2
                                        AND NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)
                                        GROUP BY
                                                 `standard_id`) AS `outgoing_materials`"), 'outgoing_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_material_types', 'q3w_material_types.id', '=', 'q3w_material_standards.material_type')
            ->where(function ($query) {
                $query->orWhereNotNull('coming_to_materials.destination_project_object_id')
                    ->orWhereNotNull('outgoing_materials.source_project_object_id');
            })
            ->select([
                'q3w_material_standards.id as id',
                'q3w_material_standards.name as standard_name',
                'q3w_material_types.accounting_type',
                DB::raw('IFNULL(CASE WHEN `q3w_material_types`.`accounting_type` = 1 AND `coming_to_material_amount` > 1 THEN 1 ELSE `coming_to_material_amount` END, 0) as `coming_to_material_amount`'),
                DB::raw('IFNULL(`coming_to_material_quantity`, 0) as `coming_to_material_quantity`'),
                DB::raw('IFNULL(round((`coming_to_material_quantity` * `q3w_material_standards`.`weight`), 3), 0) as `coming_to_material_weight`'),
                DB::raw('IFNULL(CASE WHEN `q3w_material_types`.`accounting_type` = 1 AND `outgoing_material_amount` > 1 THEN 1 ELSE `outgoing_material_amount` END, 0) as `outgoing_material_amount`'),
                DB::raw('IFNULL(`outgoing_material_quantity`, 0) as `outgoing_material_quantity`'),
                DB::raw('IFNULL(round((`outgoing_material_quantity` * `q3w_material_standards`.`weight`), 3), 0) as `outgoing_material_material_weight`')
            ])
            ->orderBy('q3w_material_standards.material_type')
            ->orderBy('q3w_material_standards.name');
    }

    function getObjectsRemainsQuery() 
    {
        return (new q3wMaterial)
        //->dxLoadOptions()
        ->leftJoin('q3w_material_standards', 'q3w_materials.standard_id', '=', 'q3w_material_standards.id')
        ->leftJoin('project_objects', 'q3w_materials.project_object', '=', 'project_objects.id')
        ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', '=', 'q3w_material_types.id')
        ->leftJoin('q3w_measure_units', 'q3w_material_types.measure_unit', '=', 'q3w_measure_units.id')
        ->leftJoin('q3w_material_comments', 'q3w_materials.comment_id', '=', 'q3w_material_comments.id')
        ->select([
            'q3w_materials.*',
            'q3w_material_standards.name as standard_name',
            'project_objects.name as object_name',
            'q3w_measure_units.value as unit_measure_value',
            'q3w_material_comments.comment as comment',
            DB::Raw('ROUND(`q3w_material_standards`.`weight` * `amount` * `quantity`, 3) as `summary_weight`')
        ])
        ->where([['amount', '>', 0], ['quantity', '>', 0]])
        ->orderBy('q3w_materials.project_object')
        ->orderBy('standard_name');
            
    }

    public function materialRemainsList(Request $request): string
    {
        $options = json_decode($request['data']);
        $projectObjectId = $request["projectObjectId"];
        $requestedDate = $request["requestedDate"];

        $materialsList = $this->getMaterialRemainsQuery($projectObjectId, $requestedDate, $options)
            ->get();

        return json_encode(array(
            "data" => $materialsList,
            "totalCount" => $materialsList->count()
            ),
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }


    public function objectsRemainsList(Request $request): string
    {

        $materialsList = $this->getObjectsRemainsQuery()
            ->get();
        
        return json_encode(array(
            "data" => $materialsList,
            "totalCount" => $materialsList->count()
            ),
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function exportMaterialRemains(Request $request)
    {
        $filterText = json_decode($request->input('filterList'));
        $options = json_decode($request['filterOptions']);
        $projectObjectId = json_decode($request["projectObjectId"]);
        $requestedDate = json_decode($request["requestedDate"]);

        $materialsList = $this->getMaterialRemainsQuery($projectObjectId, $requestedDate, $options)
            ->get()
            ->toArray();

        return (new MaterialRemainsXLSXReport($projectObjectId, $materialsList, $filterText, $requestedDate))->export();
    }

    public function exportObjectsRemains(Request $request)
    {
        $filterText = json_decode($request->input('filterList'));
        $options = json_decode($request['filterOptions']);
        $projectObjectId = json_decode($request["projectObjectId"]);
        $requestedDate = json_decode($request["requestedDate"]);

        $materialsList = $this->getObjectsRemainsQuery()
            ->get()
            ->toArray();

        return (new MaterialObjectsRemainsXLSXReport($projectObjectId, $materialsList, $filterText, $requestedDate))->export();
    }
}
