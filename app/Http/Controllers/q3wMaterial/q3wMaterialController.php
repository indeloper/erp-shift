<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterial;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialComment;
use App\models\q3wMaterial\q3wMaterialSnapshot;
use App\models\q3wMaterial\q3wMaterialSnapshotMaterial;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Services\q3wMaterialAccounting\Reports\MaterialTableXLSXReport;
use Illuminate\Database\Eloquent\Model;
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
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;
        }

        return view('materials.materials')->with([
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => q3wMaterialStandard::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name', 'address')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjectId' => $projectObjectId
        ]);
    }

    public function table(Request $request)
    {
        return view('materials.material-table')->with([
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => q3wMaterialStandard::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name', 'address')->toJson(JSON_UNESCAPED_UNICODE)
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
        return q3wMaterialSnapshot::where('project_object_id', '=', $projectObjectId)
            ->leftJoin('q3w_material_operations', 'operation_id', 'q3w_material_operations.id')
            ->orderBy('q3w_material_snapshots.created_at', 'desc')
            ->get(['q3w_material_snapshots.id',
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

    public function actualProjectObjectMaterialsList(Request $request){
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
            ->where('a.project_object', '=', $projectObjectId)
            ->where('amount', '<>', 0)
            ->where('quantity', '<>', 0)
            ->get(['a.*',
                'f.comment',
                'b.name as standard_name',
                'b.material_type',
                'b.weight',
                'd.accounting_type',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value',])
            ->toArray();

        foreach ($activeOperationMaterials as $operationMaterial){
            foreach ($materials as $material){
                switch ($operationMaterial->accounting_type) {
                    case 2:
                        if (($operationMaterial->standard_id == $material->standard_id)
                            && ($operationMaterial->quantity == $material->quantity)
                            && ($operationMaterial->comment == $material->comment)) {
                            $material->amount -= $operationMaterial->amount;
                            if ($material->amount <= 0) {
                                unset($material);
                            }
                        }
                        break;
                    default:
                        if ($operationMaterial->standard_id == $material->standard_id) {
                            $material->quantity -= $operationMaterial->quantity * $operationMaterial->amount;
                        }
                }
            }
        }

        return json_encode($materials, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function allProjectObjectMaterialsWithActualAmountList(Request $request){
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;
        }

        return DB::table('q3w_material_standards as a')
            ->leftJoin('q3w_materials as b', function($join) use ($projectObjectId) {
                $join->on('a.id', '=', 'b.standard_id');
                $join->on('b.project_object','=',   DB::RAW($projectObjectId));
            })
            ->leftJoin('q3w_material_types as d', 'a.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->get(['a.id',
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
                DB::RAW('0 as from_operation')])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
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
            ->get(['a.id',
                'a.standard_id',
                'a.quantity',
                'a.amount',
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

        foreach ($activeOperationMaterials as $operationMaterial){
            foreach ($materials as $material){
                switch ($operationMaterial->accounting_type) {
                    case 2:
                        if (($operationMaterial->standard_id == $material->standard_id) && ($operationMaterial->quantity == $material->quantity)) {
                            if ($operationMaterial->amount_modifier < 0) {
                                $material->amount += $operationMaterial->amount * $operationMaterial->amount_modifier;
                            }
                        }
                        break;
                    default:
                        if ($operationMaterial->standard_id == $material->standard_id) {
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


        return q3wOperationMaterial::leftJoin('q3w_material_operations as a', 'q3w_operation_materials.material_operation_id', '=', 'a.id')
            ->leftJoin('q3w_material_standards as b', 'q3w_operation_materials.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_operation_material_comments as f', 'comment_id', '=', 'f.id')
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
                $query->where('a.source_project_object_id', $projectObject->id)
                    ->orWhere('a.destination_project_object_id', $projectObject->id);
            })
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
            ->whereIn('a.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->orderBy('a.created_at', 'desc')
            ->get(['q3w_operation_materials.*',
                'a.id as operation_id',
                'a.operation_route_id',
                'a.source_project_object_id',
                'a.destination_project_object_id',
                'a.created_at as operation_date',
                'd.measure_unit',
                'd.name as material_type_name',
                'e.value as measure_unit_value'])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function reservedMaterialsList(Request $request) {
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = ProjectObject::whereNotNull('short_name')->get(['id'])->first()->id;
        }

        return DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f', 'a.material_operation_id', '=', 'f.id')
            ->where('f.source_project_object_id', $projectObjectId)
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
            ->whereRaw('IFNULL(`transfer_operation_stage_id`, 0) NOT IN (2, 3) ')
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->whereNotIn('f.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
            ->get(['a.id',
                'a.standard_id',
                'a.quantity',
                'a.amount',
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

    public function materialsTableList(Request $request): string
    {
        $options = json_decode($request['data']);

        $materialsList = (new q3wMaterialSnapshotMaterial)->dxLoadOptions($options)
            ->leftJoin('q3w_material_snapshots', 'q3w_material_snapshot_materials.snapshot_id', '=', 'q3w_material_snapshots.id')
            ->leftJoin('q3w_material_standards', 'q3w_material_snapshot_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', '=', 'q3w_material_types.id')
            ->leftJoin('q3w_measure_units', 'q3w_material_types.measure_unit', '=', 'q3w_measure_units.id')
            ->leftJoin('project_objects', 'q3w_material_snapshots.project_object_id', '=', 'project_objects.id')
            ->select(['q3w_material_snapshot_materials.id',
                'q3w_material_snapshot_materials.standard_id',
                'q3w_material_snapshot_materials.quantity',
                'q3w_material_snapshot_materials.amount',
                'q3w_material_snapshots.created_at as snapshot_date',
                DB::raw('MAX(`q3w_material_snapshots`.`created_at`) OVER (PARTITION BY `project_objects`.`id`) as `max_snapshot_date`'),
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.material_type',
                'q3w_material_standards.weight',
                'q3w_material_types.accounting_type',
                'q3w_material_types.measure_unit',
                'q3w_material_types.name as material_type_name',
                'q3w_measure_units.value as measure_unit_value',
                'project_objects.short_name as project_object_short_name']);

        return DB::table(DB::raw('('.Str::replaceArray('?', $materialsList->getBindings(), $materialsList->toSql()).') as TEMP'))
            ->where('snapshot_date', '=', DB::raw('`max_snapshot_date`'))
            ->where('quantity', '<>', 0)
            ->where('amount', '<>', 0)
            ->get(['*'])
            ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function printMaterialsTable(Request $request) {
        $filterOptions = json_decode($request->input('filterOptions'));
        $filterList = json_decode($request->input('filterList'));

        $materialsList = (new q3wMaterialSnapshotMaterial)
            ->dxLoadOptions($filterOptions)
            ->leftJoin('q3w_material_snapshots', 'q3w_material_snapshot_materials.snapshot_id', '=', 'q3w_material_snapshots.id')
            ->leftJoin('q3w_material_standards', 'q3w_material_snapshot_materials.standard_id', '=', 'q3w_material_standards.id')
            ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', '=', 'q3w_material_types.id')
            ->leftJoin('q3w_measure_units', 'q3w_material_types.measure_unit', '=', 'q3w_measure_units.id')
            ->leftJoin('project_objects', 'q3w_material_snapshots.project_object_id', '=', 'project_objects.id')
            ->select(['q3w_material_snapshot_materials.id',
                'q3w_material_snapshot_materials.standard_id',
                'q3w_material_snapshot_materials.quantity',
                'q3w_material_snapshot_materials.amount',
                'q3w_material_snapshots.created_at as snapshot_date',
                DB::raw('MAX(`q3w_material_snapshots`.`created_at`) OVER (PARTITION BY `project_objects`.`id`) as `max_snapshot_date`'),
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.material_type',
                'q3w_material_standards.weight',
                'q3w_material_types.accounting_type',
                'q3w_material_types.measure_unit',
                'q3w_material_types.name as material_type_name',
                'q3w_measure_units.value as measure_unit_value',
                'project_objects.short_name as project_object_short_name',
                'project_objects.address as project_object_address']);

        $groupedMaterials = DB::table(DB::raw('('.Str::replaceArray('?', $materialsList->getBindings(), $materialsList->toSql()).') as TEMP'))
            ->where('snapshot_date', '=', DB::raw('`max_snapshot_date`'))
            ->where('quantity', '<>', 0)
            ->where('amount', '<>', 0)
            ->orderBy('standard_name')
            ->orderBy('quantity')
            ->orderBy('amount')
            ->get(['*'])
            ->groupBy(['project_object_short_name', 'material_type_name', 'standard_name'])
            ->toArray();



        return (new MaterialTableXLSXReport($groupedMaterials, $filterList))->export();

        /*return view('materials.print-material-table')
            ->with([
                'materials' => $groupedMaterials,
                'filterList' => $filterList
            ]);*/
    }
}
