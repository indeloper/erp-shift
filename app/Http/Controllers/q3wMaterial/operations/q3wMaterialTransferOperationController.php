<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\Notification;
use App\Models\ProjectObject;
use App\models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\models\q3wMaterial\q3wMaterial;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\models\q3wMaterial\q3wMaterialSnapshot;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\User;
use http\Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class q3wMaterialTransferOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function create(Request $request)
    {
        $transferOperationInitiator = "none";

        if (isset($request->sourceProjectObjectId)) {
            $sourceProjectObjectId = $request->sourceProjectObjectId;
            $transferOperationInitiator = "source";
        } else {
            $sourceProjectObjectId = 0;
        }

        if (isset($request->destinationProjectObjectId)) {
            $destinationProjectObjectId = $request->destinationProjectObjectId;
            $transferOperationInitiator = "destination";
        } else {
            $destinationProjectObjectId = 0;
        }

        if (isset($request->materialsToTransfer)) {
            $predefinedMaterialsArray = explode('+', $request->materialsToTransfer);

            $predefinedMaterials = DB::table('q3w_materials as a')
                ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
                ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
                ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
                ->where('a.project_object', '=', $sourceProjectObjectId)
                ->whereIn('a.id', $predefinedMaterialsArray)
                ->get(['a.id',
                    'a.standard_id',
                    'b.name as standard_name',
                    'b.material_type',
                    'b.weight as standard_weight',
                    'd.accounting_type',
                    'd.measure_unit',
                    'e.value as measure_unit_value',
                    DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`quantity` END AS `length_quantity`'),
                    DB::raw('CASE WHEN `d`.`accounting_type` = 1 THEN `a`.`amount` ELSE `a`.`quantity` END AS `material_quantity`')])
                ->toJSON(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        } else {
            $predefinedMaterials = json_encode([]);
        }


        return view('materials.operations.transfer.new')->with([
            'sourceProjectObjectId' => $sourceProjectObjectId,
            'destinationProjectObjectId' => $destinationProjectObjectId,
            'transferOperationInitiator' => $transferOperationInitiator,
            'currentUserId' => Auth::id(),
            'predefinedMaterials' => $predefinedMaterials
        ]);
    }

    public function moveOperationToNextStage($operationID)
    {
        $operation = q3wMaterialOperation::findOrFail($operationID);

        if (isset($operation)) {
            switch ($operation->operation_route_stage_id) {
                case 5:
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                    $notification->update([
                        'name' => 'Новое перемещение',
                        'user_id' => $operation->source_responsible_user_id,
                        //'task_id' => $task->id,
                        //'contractor_id' => $task->contractor_id,
                        //'project_id' => $task->project_id,
                        //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        //'type' => 2
                    ]);
                    $operation->operation_route_stage_id = 7;
                    $operation->save();
                    break;
                case 6:
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на операцию: ' . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
                    $notification->update([
                        'name' => 'Новое перемещение',
                        'user_id' => $operation->destination_responsible_user_id,
                        //'task_id' => $task->id,
                        //'contractor_id' => $task->contractor_id,
                        //'project_id' => $task->project_id,
                        //'object_id' => isset($task->project->object->id) ? $task->project->object->id : null,
                        'created_at' => now(),
                        //'type' => 2
                    ]);
                    $operation->operation_route_stage_id = 8;
                    $operation->save();
                    break;
            }
        }

        //$this->moveOperationToNextStage($operation->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //TODO Нужно разобраться с механизмом искллючений и откатом транзакции
        DB::beginTransaction();
        $requestData = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

        $operationRouteStage = 4;

        if ($requestData['transfer_operation_initiator'] == 'none' || $requestData['transfer_operation_initiator'] == 'source') {
            $operationRouteStage = 6;
        }

        if ($requestData['transfer_operation_initiator'] == 'destination') {
            $operationRouteStage = 5;
        }

        //TODO выделить куда-нибудь в функцию, переписать, чтоб по эталону брался тип учета
        //Нужно проверить, что материал существует
        //Нужно проверить, что остаток будет большим, или равным нулю
        foreach ($requestData['materials'] as $inputMaterial) {
            if ($inputMaterial['accounting_type'] == 1) {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['source_project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->where('quantity', $inputMaterial['length_quantity'])
                    ->firstOrFail();

                if ($inputMaterial['material_quantity'] > $sourceMaterial['amount']) {
                    abort(400, 'Bad quantity for standard ' . $inputMaterial['standard_id']);
                }

            }
        }

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 2,
            'operation_route_stage_id' => $operationRouteStage,
            'source_project_object_id' => $requestData['source_project_object_id'],
            'destination_project_object_id' => $requestData['destination_project_object_id'],
            'date_start' => $requestData['date_start'],
            'date_end' => $requestData['date_end'],
            'creator_user_id' => Auth::id(),
            'source_responsible_user_id' => $requestData['source_responsible_user_id'],
            'destination_responsible_user_id' => $requestData['destination_responsible_user_id'],
        ]);

        $materialOperation->save();

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);
            $materialType = q3wMaterialType::findOrFail($materialStandard->material_type);

            $inputMaterialAmount = $materialType->accounting_type == 1 ? $inputMaterial['material_quantity'] : null;
            $inputMaterialQuantity = $materialType->accounting_type == 1 ? $inputMaterial['length_quantity'] : $inputMaterial['material_quantity'];

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $inputMaterialAmount,
                'quantity' => $inputMaterialQuantity
            ]);

            $operationMaterial->save();

            /*if ($materialType -> accounting_type == 1) {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $materialStandard -> id)
                    ->where ('quantity', $inputMaterialQuantity)
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $materialStandard -> id)
                    ->first();
            }

            if (isset($material)) {
                if ($materialType -> accounting_type == 1) {
                    $material -> amount = $material -> amount + $inputMaterialAmount;
                } else {
                    $material -> quantity = $material -> quantity + $inputMaterialQuantity;
                }

                $material -> save();
            } else {
                $material = new q3wMaterial([
                   'standard_id' => $materialStandard -> id,
                   'project_object' => $requestData['project_object_id'],
                   'amount' => $inputMaterialAmount,
                   'quantity' => $inputMaterialQuantity
                ]);

                $material -> save();
            }*/
        }

        //(new q3wMaterialSnapshot)->takeSnapshot($materialOperation, ProjectObject::find($requestData['project_object_id']));

        DB::commit();

        $this->moveOperationToNextStage($materialOperation->id);

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    public function materialCanBeWrittenOffFromSourceObject(int $sourceProjectObjectID, q3wMaterial $material)
    {
        $sourceProjectObjectMaterial = (new q3wMaterial)
            ->where('project_object_id', $sourceProjectObjectID)
            ->where('standard_id', $material->standard_id)
            ->where('amount', $material->amount);

        if (!isset($sourceProjectObjectMaterial)) {
            return false;
        }

        if ($material->quantity > $sourceProjectObjectMaterial->quantity) {
            return false;
        }

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function show(q3wMaterialOperation $q3wMaterialOperation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function edit(q3wMaterialOperation $q3wMaterialOperation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function update(Request $request, q3wMaterialOperation $q3wMaterialOperation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param q3wMaterialOperation $q3wMaterialOperation
     * @return Response
     */
    public function destroy(q3wMaterialOperation $q3wMaterialOperation)
    {
        //
    }
}
