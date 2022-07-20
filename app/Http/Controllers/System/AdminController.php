<?php

namespace App\Http\Controllers\System;

use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterialSnapshot;
use App\Models\q3wMaterial\q3wMaterialSnapshotMaterial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Psy\Util\Json;

class AdminController extends Controller
{
    public function admin()
    {
        return view('support.admin');
    }

    public function sendTechUpdateNotify(Request $request)
    {
        $start_date_parsed = Carbon::parse($request->start_date)->isoFormat('D.MM.YYYY');
        $finish_date_parsed = Carbon::parse($request->finish_date)->isoFormat('D.MM.YYYY');

        Artisan::call("send:notify {$start_date_parsed} {$request->start_time} {$finish_date_parsed} {$request->finish_time}");

        return back();
    }

    public function loginAsUserId(Request $request)
    {
        if (auth()->user()->is_su) {
            auth()->login(User::findOrFail($request->user_id), false);
        }
        return redirect('/');
    }

    public function validateMaterialAccountingData(Request $request)
    {
        $projectObjectId = $request->projectObjectId ?? ProjectObject::whereNotNull('short_name')
                ->orderBy("short_name")
                ->get(['id'])
                ->first()->id;

        return view('admin.validate-material-accounting-data')->with([
            'projectObjectId' => $projectObjectId
        ]);
    }

    public function getMaterialAccountingDataValidationResult(Request $request)
    {
        $errors = [];
        $accumulativeMaterials = [];

        $projectObjectId = (int)$request->projectObjectId;

        $operationList = q3wMaterialOperation::where(function ($query) use ($projectObjectId) {
            $query->where('source_project_object_id', $projectObjectId)
                ->orWhere('destination_project_object_id', $projectObjectId);
        })
            ->whereIn('operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->leftJoin('q3w_material_snapshots', 'q3w_material_snapshots.operation_id', '=', 'q3w_material_operations.id')
            //->leftJoin('q3w_operation_routes', 'q3w_material_operations.operation_id', '=', 'q3w_operation_routes.id')
            ->distinct()
            ->orderBy('q3w_material_snapshots.created_at')
            ->get(['q3w_material_operations.*']);

        foreach ($operationList as $operation) {
            $operationSnapshot = q3wMaterialSnapshot::where('project_object_id', $projectObjectId)
                ->where('operation_id', $operation->id)
                ->first();

            if (!isset($operationSnapshot)) {
                $errors[] = ['operation_id' => $operation->id, 'error' => 'snapshot is not set'];
            }

            $operationMaterials = q3wOperationMaterial::where('material_operation_id', $operation->id)
                ->leftJoin('q3w_material_standards', 'q3w_operation_materials.standard_id', '=', 'q3w_material_standards.id')
                ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', '=', 'q3w_material_types.id')
                ->where(function ($query) {
                    $query->where('edit_states', 'not like', '%deleted%')
                        ->orWhereNull('edit_states');
                })
                ->orderBy('q3w_operation_materials.standard_id')
                ->get([
                    'q3w_operation_materials.standard_id',
                    'q3w_material_standards.name as standard_name',
                    'q3w_operation_materials.quantity',
                    'q3w_operation_materials.amount',
                    'q3w_material_standards.weight',
                    'q3w_material_types.accounting_type'
                ]);

            foreach ($operationMaterials as $operationMaterial) {
                switch ($operationMaterial->accounting_type){
                    case 2:
                        $accumulativeMaterialsId = "id_" . $operationMaterial->standard_id . "_" . $operationMaterial->quantity;
                        break;
                    default:
                        $accumulativeMaterialsId = "id_" . $operationMaterial->standard_id;
                }

                $accumulativeDelta = 0;

                switch ($operation->operation_route_id) {
                    case 1:
                        $accumulativeDelta = 1;
                        break;
                    case 2:
                        if ($operation->source_project_object_id == $projectObjectId) {
                            $accumulativeDelta = -1;
                        } elseif ($operation->destination_project_object_id == $projectObjectId) {
                            $accumulativeDelta = 1;
                        }
                        break;
                    case 3:
                        switch($operationMaterial->transform_operation_stage_id) {
                            case 1:
                            case 4:
                                $accumulativeDelta = -1;
                                break;
                            case 2:
                            case 3:
                                $accumulativeDelta = 1;
                                break;
                        }
                        break;
                    case 4:
                        $accumulativeDelta = -1;
                        break;
                }

                $weight = ($operationMaterial->quantity * $operationMaterial->amount * $operationMaterial->weight) * $accumulativeDelta;

                if (isset($accumulativeMaterials[$accumulativeMaterialsId])) {
                    $accumulativeMaterials[$accumulativeMaterialsId]["weight"] += $weight;
                } else {
                    $accumulativeMaterials[$accumulativeMaterialsId] = [
                        "standard_id" => $operationMaterial->standard_id,
                        "standard_name" => $operationMaterial->standard_name,
                        "quantity" => $operationMaterial->quantity,
                        "amount" => $operationMaterial->amount,
                        "weight" => $weight
                    ];
                }
            }

            $snapshotMaterials = q3wMaterialSnapshotMaterial::where('snapshot_id', $operationSnapshot->id)
                ->leftJoin('q3w_material_standards', 'q3w_material_snapshot_materials.standard_id', '=', 'q3w_material_standards.id')
                ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', '=', 'q3w_material_types.id')
                ->select([
                    'q3w_material_snapshot_materials.standard_id',
                    'q3w_material_snapshot_materials.quantity',
                    DB::Raw('sum(q3w_material_snapshot_materials.amount) as amount'),
                    'q3w_material_standards.weight',
                    'q3w_material_types.accounting_type'
                ])
                ->groupBy([
                    'q3w_material_snapshot_materials.standard_id',
                    'q3w_material_snapshot_materials.quantity',
                    'q3w_material_standards.weight',
                    'q3w_material_types.accounting_type'
                ])
                ->get();

            $snapshotMaterialsData = [];

            foreach ($snapshotMaterials as $snapshotMaterial) {
                switch ($snapshotMaterial->accounting_type){
                    case 2:
                        $snapshotMaterialId = "id_" . $snapshotMaterial->standard_id . "_" . $snapshotMaterial->quantity;
                        break;
                    default:
                        $snapshotMaterialId = "id_" . $snapshotMaterial->standard_id;
                }

                $snapshotMaterialsData[$snapshotMaterialId] = [
                    "standard_id" => $snapshotMaterial->standard_id,
                    "quantity" => $snapshotMaterial->quantity,
                    "amount" => $snapshotMaterial->amount,
                    "weight" => round($snapshotMaterial->quantity * $snapshotMaterial->amount * $snapshotMaterial->weight, 3)
                ];
            }

            foreach ($accumulativeMaterials as $key => $accumulativeMaterial) {
                if (isset($snapshotMaterialsData[$key])) {
                    if (round($accumulativeMaterial["weight"], 3) != $snapshotMaterialsData[$key]["weight"]) {
                        $errors[] = [
                            'operation_id' => $operation->id,
                            'operationUrl' => $operation->url,
                            'errorCode' => 1,
                            'standardID' => $accumulativeMaterial["standard_id"],
                            'standardName' => $accumulativeMaterial["standard_name"],
                            'quantity' => $accumulativeMaterial["quantity"],
                            'amount' => $accumulativeMaterial["amount"],
                            'accumulativeMaterialWeight' => round($accumulativeMaterial["weight"], 3),
                            'snapshotWeight' => $snapshotMaterialsData[$key]["weight"],
                            'error' => 'difference in weight (' . $key . '). accumulative material weight = ' . round($accumulativeMaterial["weight"], 3) . '; snapshot weight = ' . $snapshotMaterialsData[$key]["weight"]
                        ];

                    }
                } else {
                    $errors[] = ['operation_id' => $operation->id, 'error' => 'material with key ' . $key . ' not found in snapshot', ];
                }
            }
        }

        return json_encode($errors, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
