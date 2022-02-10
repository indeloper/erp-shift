<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialComment;
use App\Models\q3wMaterial\q3wMaterialSnapshot;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wOperationMaterialComment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class q3wMaterialTransferOperationController extends Controller
{
    const EMPTY_COMMENT_TEXT = "Комментарий не указан";
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
                ->leftJoin('q3w_material_comments as f', 'a.comment_id', '=', 'f.id')
                ->where('a.project_object', '=', $sourceProjectObjectId)
                ->whereIn('a.id', $predefinedMaterialsArray)
                ->get(['a.id',
                    'a.standard_id',
                    'a.amount',
                    'a.amount as total_amount',
                    'a.quantity',
                    'a.quantity as total_quantity',
                    "a.comment_id",
                    "a.comment_id as initial_comment_id",
                    "f.comment",
                    "f.comment as initial_comment",
                    'b.name as standard_name',
                    'b.material_type',
                    'b.weight as standard_weight',
                    'd.accounting_type',
                    'd.measure_unit',
                    'e.value as measure_unit_value'])
                ->toArray();

            foreach ($predefinedMaterials as $material) {
                switch ($material->accounting_type) {
                    case 2:
                        $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                            ->where('quantity', $material->quantity)
                            ->where(function ($query) use ($material) {
                                if (empty($material->comment_id)) {
                                    $query->whereNull('initial_comment_id');
                                } else {
                                    $query->where('initial_comment_id', $material->comment_id);
                                }
                            }
                            )
                            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                            ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                            ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                            ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                            ->where('q3w_material_operations.source_project_object_id', $sourceProjectObjectId)
                            ->get(DB::raw('sum(`amount`) as amount'))
                            ->first();

                        $material->total_amount -= $activeOperationMaterialAmount->amount;
                        break;
                    default:
                        $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                            ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                            ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                            ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                            ->where('q3w_material_operations.source_project_object_id', $sourceProjectObjectId)
                            ->get(DB::raw('sum(`quantity`) as quantity'))
                            ->first();
                        $material->total_quantity = round($material->total_quantity - $activeOperationMaterialAmount->quantity, 2);
                }

                $material->validationUid = Str::uuid();
            }
        } else {
            $predefinedMaterials = [];
        }

        return view('materials.operations.transfer.new')->with([
            'sourceProjectObjectId' => $sourceProjectObjectId,
            'destinationProjectObjectId' => $destinationProjectObjectId,
            'transferOperationInitiator' => $transferOperationInitiator,
            'currentUserId' => Auth::id(),
            'predefinedMaterials' => json_encode($predefinedMaterials, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function move(q3wMaterialOperation $operation)
    {
        DB::beginTransaction();

        foreach ($operation->materials as $operationMaterial) {
            if (in_array("deletedByRecipient", json_decode($operationMaterial->edit_states))) {
                continue;
            }

            $operationMaterialComment = q3wOperationMaterialComment::find($operationMaterial->comment_id);
            $operationMaterialInitialComment = q3wMaterialComment::find($operationMaterial->initial_comment_id);

            switch ($operationMaterial->standard->materialType->accounting_type) {
                case 2:
                    $sourceProjectObjectMaterial = q3wMaterial::where('project_object', $operation->source_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->where('quantity', $operationMaterial->quantity)
                        ->where(function ($query) use ($operationMaterial) {
                            if (empty($operationMaterial->initial_comment_id)) {
                                $query->whereNull('comment_id');
                            } else {
                                $query->where('comment_id', $operationMaterial->initial_comment_id);
                            }
                        })
                        ->first();

                    $destinationProjectObjectMaterial = q3wMaterial::leftJoin('q3w_material_comments', 'comment_id', '=', 'q3w_material_comments.id')
                        ->where('project_object', $operation->destination_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->where('quantity', $operationMaterial->quantity)
                        ->where(function ($query) use ($operationMaterial, $operationMaterialComment, $operationMaterialInitialComment) {
                            if (empty($operationMaterialComment->comment)) {
                                if (empty($operationMaterial->initial_comment_id)) {
                                    $query->whereNull('comment_id');
                                } else {
                                    $query->where('comment', 'like', $operationMaterialInitialComment->comment);
                                }
                            } else {
                                $query->where('comment', 'like', $operationMaterialComment->comment);
                            }
                        })
                        ->get(['q3w_materials.*', 'q3w_material_comments.comment'])
                        ->first();
                    break;
                default:
                    $sourceProjectObjectMaterial = q3wMaterial::where('project_object', $operation->source_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->where(function ($query) use ($operationMaterial) {
                            if (empty($operationMaterial->initial_comment_id)) {
                                $query->whereNull('comment_id');
                            } else {
                                $query->where('comment_id', $operationMaterial->initial_comment_id);
                            }
                        })
                        ->first();

                    $destinationProjectObjectMaterial = q3wMaterial::leftJoin('q3w_material_comments', 'comment_id', '=', 'q3w_material_comments.id')
                        ->where('project_object', $operation->destination_project_object_id)
                        ->where('standard_id', $operationMaterial->standard->id)
                        ->where(function ($query) use ($operationMaterial, $operationMaterialComment, $operationMaterialInitialComment) {
                            if (empty($operationMaterialComment->comment)) {
                                if (empty($operationMaterial->initial_comment_id)) {
                                    $query->whereNull('comment_id');
                                } else {
                                    $query->where('comment', 'like', $operationMaterialInitialComment->comment);
                                }
                            } else {
                                $query->where('comment', 'like', $operationMaterialComment->comment);
                            }
                        })
                        ->get(['q3w_materials.*', 'q3w_material_comments.comment'])
                        ->first();
            }

            if (!isset($sourceProjectObjectMaterial)) {
                DB::rollBack();
                abort(400, 'На объекте отправления материал не существует');
            }

            switch ($operationMaterial->standard->materialType->accounting_type) {
                case 2:
                    $sourceProjectObjectMaterialDelta = $sourceProjectObjectMaterial->amount - $operationMaterial->amount;
                    break;
                default:
                    $sourceProjectObjectMaterialDelta = round($sourceProjectObjectMaterial->quantity - $operationMaterial->amount * $operationMaterial->quantity, 2);
            }

            if ($sourceProjectObjectMaterialDelta < 0) {
                DB::rollBack();
                abort(400, 'На объекте отправления недостаточно материала');
            }

            switch ($operationMaterial->standard->materialType->accounting_type) {
                case 2:
                    $sourceProjectObjectMaterial->amount = $sourceProjectObjectMaterial->amount - $operationMaterial->amount;
                    $sourceProjectObjectMaterial->save();

                    if (isset($destinationProjectObjectMaterial)) {
                        $destinationProjectObjectMaterial->amount = $destinationProjectObjectMaterial->amount + $operationMaterial->amount;
                        $destinationProjectObjectMaterial->save();
                    } else {
                        if (empty($operationMaterialComment->comment)) {
                            $materialCommentId = null;
                        } else {
                            $materialComment = new q3wMaterialComment([
                                'comment' => $operationMaterialComment->comment,
                                'author_id' => Auth::id()
                            ]);
                            $materialComment->save();
                            $materialCommentId = $materialComment->id;
                        }

                        $destinationProjectObjectMaterial = new q3wMaterial([
                            'standard_id' => $operationMaterial->standard->id,
                            'project_object' => $operation->destination_project_object_id,
                            'amount' => $operationMaterial->amount,
                            'quantity' => $operationMaterial->quantity,
                            'comment_id' => $materialCommentId,

                        ]);
                        $destinationProjectObjectMaterial->save();
                    }
                    break;
                default:
                    $sourceProjectObjectMaterial->quantity = ($sourceProjectObjectMaterial->amount * $sourceProjectObjectMaterial->quantity) - ($operationMaterial->amount * $operationMaterial->quantity);
                    $sourceProjectObjectMaterial->amount = 1;
                    $sourceProjectObjectMaterial->save();

                    if (isset($destinationProjectObjectMaterial)) {
                        $destinationProjectObjectMaterial->quantity = ($destinationProjectObjectMaterial->quantity * $destinationProjectObjectMaterial->amount) + ($operationMaterial->amount * $operationMaterial->quantity);
                        $sourceProjectObjectMaterial->amount = 1;
                        $destinationProjectObjectMaterial->save();
                    } else {
                        if (empty($operationMaterialComment->comment)) {
                            $materialCommentId = null;
                        } else {
                            $materialComment = new q3wMaterialComment([
                                'comment' => $operationMaterialComment->comment,
                                'author_id' => Auth::id()
                            ]);
                            $materialComment->save();
                            $materialCommentId = $materialComment->id;
                        }

                        $destinationProjectObjectMaterial = new q3wMaterial([
                            'standard_id' => $operationMaterial->standard->id,
                            'project_object' => $operation->destination_project_object_id,
                            'amount' => 1,
                            'quantity' => $operationMaterial->amount * $operationMaterial->quantity,
                            'comment_id' => $materialCommentId
                        ]);
                        $destinationProjectObjectMaterial->save();
                    }
            }
        }

        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->source_project_object_id));
        (new q3wMaterialSnapshot)->takeSnapshot($operation, ProjectObject::find($operation->destination_project_object_id));

        DB::commit();
    }

    public function moveOperationToNextStage($operationId, $moveToConflict, $cancelled = false)
    {
        $operation = q3wMaterialOperation::findOrFail($operationId);

        if (isset($operation)) {
            switch ($operation->operation_route_stage_id) {
                // Маршрут от отправителя

                case 5: //Уведомление получателю
                    $operation->operation_route_stage_id = 6;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Новое перемещение', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    break;
                case 6: //Ожидание получателя
                    if ($cancelled){
                        $operation->operation_route_stage_id = 45;
                    } else {
                        if ($moveToConflict) {
                            $operation->operation_route_stage_id = 9;
                        } else {
                            $this->move($operation);
                            $operation->operation_route_stage_id = 7;
                        }
                    }
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 7:
                    $operation->operation_route_stage_id = 8;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    break;
                case 9:
                    $operation->operation_route_stage_id = 43;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт в операции', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 43:
                    $operation->operation_route_stage_id = 10;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт в операции', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 10:
                    $operation->operation_route_stage_id = 11;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Конфликт в операции', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    break;
                case 11:
                    if ($cancelled){
                        $operation->operation_route_stage_id = 45;
                    } else {
                        if ($moveToConflict) {
                            $operation->operation_route_stage_id = 16;
                        } else {
                            $this->move($operation);
                            $operation->operation_route_stage_id = 12;
                        }
                    }
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 12:
                    $operation->operation_route_stage_id = 13;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено после конфликта. Отправитель подтверил корректность изменений.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 13:
                    $operation->operation_route_stage_id = 14;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Перемещение завершено после конфликта. Отправитель подтверил корректность изменений.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 14:
                    $operation->operation_route_stage_id = 15;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Перемещение завершено после конфликта. Отправитель подтверил корректность изменений.', $operation->destination_project_object_id);
                    break;
                case 16:
                    $operation->operation_route_stage_id = 17;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Конфликт поставлен под контроль руководителя получателя.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 17:
                    $operation->operation_route_stage_id = 18;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт поставлен под контроль руководителя получателя.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 18:
                    $operation->operation_route_stage_id = 19;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт поставлен под контроль руководителя получателя.', $operation->source_project_object_id);
                    break;
                case 19:
                    if ($cancelled){
                        $operation->operation_route_stage_id = 45;
                    } else {
                        $this->move($operation);
                        $operation->operation_route_stage_id = 20;
                    }
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 20:
                    $operation->operation_route_stage_id = 21;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено руководителем получателя.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 21:
                    $operation->operation_route_stage_id = 22;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Перемещение завершено руководителем получателя.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 22:
                    $operation->operation_route_stage_id = 23;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено руководителем получателя.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;

                // Маршрут от получателя
                case 24: //Уведомление отправителю
                    $operation->operation_route_stage_id = 25;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Новое перемещение', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    break;
                case 25: //Ожидание отправителя
                    if ($cancelled){
                        $operation->operation_route_stage_id = 45;
                    } else {
                        if ($moveToConflict) {
                            $operation->operation_route_stage_id = 28;
                        } else {
                            $this->move($operation);
                            $operation->operation_route_stage_id = 26;
                        }
                    }
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 26:
                    $operation->operation_route_stage_id = 27;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    break;
                case 28:
                    $operation->operation_route_stage_id = 44;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт в операции', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 44:
                    $operation->operation_route_stage_id = 29;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт в операции', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 29:
                    $operation->operation_route_stage_id = 30;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Конфликт в операции', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    break;
                case 30:
                    if ($cancelled){
                        $operation->operation_route_stage_id = 45;
                    } else {
                        if ($moveToConflict) {
                            $operation->operation_route_stage_id = 35;
                        } else {
                            $this->move($operation);
                            $operation->operation_route_stage_id = 31;
                        }
                    }
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 31:
                    $operation->operation_route_stage_id = 32;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено после конфликта. Получатель подтверил корректность изменений.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    break;
                case 32:
                    $operation->operation_route_stage_id = 33;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Перемещение завершено после конфликта. Получатель подтверил корректность изменений.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 33:
                    $operation->operation_route_stage_id = 35;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Перемещение завершено после конфликта. Получатель подтверил корректность изменений.', $operation->source_project_object_id);
                    break;
                case 35:
                    $operation->operation_route_stage_id = 36;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Конфликт поставлен под контроль руководителя отправителя.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 36:
                    $operation->operation_route_stage_id = 37;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт поставлен под контроль руководителя отправителя.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 37:
                    $operation->operation_route_stage_id = 38;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Конфликт поставлен под контроль руководителя отправителя.', $operation->destination_project_object_id);
                    break;
                case 38:
                    if ($cancelled){
                        $operation->operation_route_stage_id = 45;
                    } else {
                        $this->move($operation);
                        $operation->operation_route_stage_id = 39;
                    }
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 39:
                    $operation->operation_route_stage_id = 40;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено руководителем отправителя.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 40:
                    $operation->operation_route_stage_id = 41;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Перемещение завершено руководителем отправителя.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 41:
                    $operation->operation_route_stage_id = 42;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Перемещение завершено руководителем отправителя.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;

                //Отмена заявки по ветке отправителя этап 6
                case 45:
                    $operation->operation_route_stage_id = 46;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 46:
                    $operation->operation_route_stage_id = 47;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 47:
                    $operation->operation_route_stage_id = 48;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                //Отмена заявки по ветке отправителя этап 11
                case 49:
                    $operation->operation_route_stage_id = 50;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 50:
                    $operation->operation_route_stage_id = 51;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 51:
                    $operation->operation_route_stage_id = 52;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                //Отмена заявки по ветке отправителя этап 19
                case 53:
                    $operation->operation_route_stage_id = 54;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 54:
                    $operation->operation_route_stage_id = 55;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 55:
                    $operation->operation_route_stage_id = 56;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;

                //Отмена заявки по ветке получателя этап 25
                case 57:
                    $operation->operation_route_stage_id = 58;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 58:
                    $operation->operation_route_stage_id = 59;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 59:
                    $operation->operation_route_stage_id = 60;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                //Отмена заявки по ветке получателя этап 30
                case 61:
                    $operation->operation_route_stage_id = 62;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 62:
                    $operation->operation_route_stage_id = 63;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->source_project_object_id);
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 63:
                    $operation->operation_route_stage_id = 64;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                //Отмена заявки по ветке полкучателя этап 38
                case 65:
                    $operation->operation_route_stage_id = 66;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->destination_responsible_user_id, $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 66:
                    $operation->operation_route_stage_id = 67;
                    $operation->save();
                    $this->sendTransferNotification($operation, 'Операция отменена.', $operation->source_responsible_user_id, $operation->destination_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
                case 67:
                    $operation->operation_route_stage_id = 68;
                    $operation->save();
                    $this->sendTransferNotificationToResponsibilityUsersOfObject($operation, 'Операция отменена.', $operation->source_project_object_id);
                    $this->moveOperationToNextStage($operation->id, $moveToConflict);
                    break;
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $requestData = json_decode($request["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

        $operationRouteStage = 4;

        if ($requestData['transfer_operation_initiator'] == 'none' || $requestData['transfer_operation_initiator'] == 'source') {
            $operationRouteStage = 5;
        }

        if ($requestData['transfer_operation_initiator'] == 'destination') {
            $operationRouteStage = 24;
        }

        //Нужно проверить, что материал существует
        //Нужно проверить, что остаток будет большим, или равным нулю
        foreach ($requestData['materials'] as $inputMaterial) {
            $sourceMaterial = q3wMaterial::where('project_object', $requestData['source_project_object_id'])
                ->where('standard_id', $inputMaterial['standard_id'])
                ->where(function ($query) use ($inputMaterial) {
                    switch ($inputMaterial['accounting_type']) {
                        case 2:
                            $query->where('quantity', '=',  $inputMaterial['quantity']);
                            break;
                    }
                })
                ->where("comment_id", $inputMaterial['initial_comment_id'])
                ->firstOrFail();

            switch($inputMaterial['accounting_type']){
                case 2:
                    if ($inputMaterial['amount'] > $sourceMaterial['amount']) {
                        abort(400, 'Bad amount for standard ' . $inputMaterial['standard_id']);
                    }
                    break;
                default:
                    if ($inputMaterial['amount'] * $inputMaterial['quantity'] > $sourceMaterial['quantity']) {
                        abort(400, 'Bad amount for standard ' . $inputMaterial['standard_id']);
                    }
            }

        }

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 2,
            'operation_route_stage_id' => $operationRouteStage,
            'source_project_object_id' => $requestData['source_project_object_id'],
            'destination_project_object_id' => $requestData['destination_project_object_id'],
            'operation_date' => isset($requestData['operation_date']) ? $requestData['operation_date'] : null,
            'creator_user_id' => Auth::id(),
            'source_responsible_user_id' => $requestData['source_responsible_user_id'],
            'destination_responsible_user_id' => $requestData['destination_responsible_user_id'],
            'consignment_note_number' => $requestData['consignment_note_number']
        ]);

        $materialOperation->save();

        if (isset($requestData['new_comment'])){
            $newComment = $requestData['new_comment'];
        } else {
            $newComment = self::EMPTY_COMMENT_TEXT;
        }

        $materialOperationComment = new q3wOperationComment([
            'material_operation_id' => $materialOperation->id,
            'operation_route_stage_id' => $materialOperation->operation_route_stage_id,
            'comment' => $newComment,
            'user_id' => Auth::id()
        ]);

        $materialOperationComment->save();

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);

            $inputMaterialAmount = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];
            $inputMaterialInitialCommentId = $inputMaterial['initial_comment_id'];

            if (empty($inputMaterial['comment'])) {
                $inputMaterialComment = null;
            } else {
                $inputMaterialComment = $inputMaterial['comment'];
            }

            if (!empty($inputMaterialComment)) {
                $materialComment = new q3wOperationMaterialComment([
                    'comment' => $inputMaterialComment,
                    'author_id' => Auth::id()
                ]);
                $materialComment->save();
                $materialCommentId = $materialComment->id;
            } else {
                $materialCommentId = null;
            }

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $inputMaterialAmount,
                'quantity' => $inputMaterialQuantity,
                'initial_amount' => $inputMaterialAmount,
                'initial_quantity' => $inputMaterialQuantity,
                'edit_states' => json_encode(['addedByInitiator']),
                'initial_comment_id' => $inputMaterialInitialCommentId,
                'comment_id' => $materialCommentId
            ]);

            $operationMaterial->save();
        }

        foreach ($requestData['uploaded_files'] as $uploadedFileId) {
            $uploadedFile = q3wOperationFile::find($uploadedFileId);
            $uploadedFile->material_operation_id = $materialOperation->id;
            $uploadedFile->operation_route_stage_id = $materialOperation->operation_route_stage_id;
            $uploadedFile->save();
        }

        DB::commit();

        $this->moveOperationToNextStage($materialOperation->id, false);

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return Application|Factory|Response|View
     */
    public function show(Request $request)
    {
        $operation = q3wMaterialOperation::findOrFail($request->operationId);
        $operationRouteStage = q3wOperationRouteStage::find($operation->operation_route_stage_id)->name;
        $transferOperationInitiator = "none";

        if (isset($operation->source_project_object_id)) {
            $sourceProjectObjectId = $operation->source_project_object_id;
            $transferOperationInitiator = "source";
        } else {
            $sourceProjectObjectId = 0;
        }

        if (isset($operation->destination_project_object_id)) {
            $destinationProjectObjectId = $operation->destination_project_object_id;
            $transferOperationInitiator = "destination";
        } else {
            $destinationProjectObjectId = 0;
        }

        $materials = DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f', 'a.material_operation_id', '=', 'f.id')
            ->leftJoin('q3w_materials as g', function($join){
                $join->on('a.standard_id', '=', 'g.standard_id');
                $join->on('f.source_project_object_id','=','g.project_object');
                $join->on(DB::raw('IF( `d`.`accounting_type` = 2,`a`.`quantity` = `g`.`quantity`, 1'), '=', DB::raw('1)'));
                $join->on(DB::raw('COALESCE(`a`.`initial_comment_id`, 0)'), '=', DB::raw('COALESCE(`g`.`comment_id`, 0)'));
            })
            ->leftJoin('q3w_material_comments as h', 'g.comment_id', '=', 'h.id')
            ->leftJoin('q3w_operation_material_comments as j', 'a.comment_id', '=', 'j.id')
            ->where('a.material_operation_id', '=', $operation->id)
            ->distinct()
            ->get(['a.id',
                'a.standard_id',
                'a.amount',
                'a.initial_amount',
                'a.quantity',
                'a.edit_states',
                'a.initial_quantity',
                'b.name as standard_name',
                'b.material_type',
                'b.weight as standard_weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value',
                'g.amount as total_amount',
                'g.quantity as total_quantity',
                'a.comment_id',
                'a.initial_comment_id',
                'j.comment',
                'h.comment as initial_comment'])
            ->toArray();

        foreach ($materials as $material) {
            $material->edit_states = json_decode($material->edit_states);

            //validationUid generating
            foreach ($materials as $materialToCheckValidationUid){
                switch ($material->accounting_type) {
                    case 2:
                        if ($material->standard_id == $materialToCheckValidationUid->standard_id and
                            $material->quantity == $materialToCheckValidationUid->quantity and
                            $material->initial_comment_id == $materialToCheckValidationUid->initial_comment_id) {
                            if (!empty($materialToCheckValidationUid->validationUid)) {
                                $material->validationUid = $materialToCheckValidationUid->validationUid;
                            } else {
                                $material->validationUid = (string) Str::uuid();
                            }
                            break 2;
                        }
                        break;
                    default:
                        if ($material->standard_id == $materialToCheckValidationUid->standard_id and
                            $material->initial_comment_id == $materialToCheckValidationUid->initial_comment_id) {
                            if (!empty($materialToCheckValidationUid->validationUid)) {
                                $material->validationUid = $materialToCheckValidationUid->validationUid;
                            } else {
                                $material->validationUid = (string) Str::uuid();
                            }
                            break 2;
                        }
                }
            }

            switch ($material->accounting_type) {
                case 2:
                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                        ->where('quantity', $material->quantity)
                        ->where('material_operation_id', '<>', $operation->id)
                        ->where(function ($query) use ($material) {
                            if (empty($material->initial_comment_id)) {
                                $query->whereNull('initial_comment_id');
                            } else {
                                $query->where('initial_comment_id', $material->initial_comment_id);
                            }
                        })
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->get();

                    $material->total_amount = $material->total_amount - $activeOperationMaterialAmount->sum('amount');
                    break;
                default:
                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                        ->where('material_operation_id', '<>', $operation->id)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->get(DB::raw('sum(`quantity`) as quantity'))
                        ->first();
                    $material->total_quantity = $material->total_quantity - $activeOperationMaterialAmount->quantity;
            }
        }

        $materials = json_encode($materials, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);

        return view('materials.operations.transfer.view')->with([
            'operationData' => $operationData,
            'operationRouteStage' => $operationRouteStage,
            'sourceProjectObjectId' => $sourceProjectObjectId,
            'destinationProjectObjectId' => $destinationProjectObjectId,
            'transferOperationInitiator' => $transferOperationInitiator,
            'operationMaterials' => $materials,
            'currentUserId' => Auth::id(),
            'allowEditing' => $this->allowEditing($operation),
            'allowCancelling' => $this->allowCancelling($operation),
            'allowMoving' => $this->allowMoving($operation),
            'routeStageId' => $operation->operation_route_stage_id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $requestData = json_decode($request["data"]);

        $operation = q3wMaterialOperation::findOrFail($requestData->operationId);

        $moveToConflict = false;

        DB::beginTransaction();
        foreach ($requestData->materials as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial->standard_id);

            if (in_array("addedByRecipient", $inputMaterial->edit_states) ||
                in_array("deletedByRecipient", $inputMaterial->edit_states)) {
                $moveToConflict = true;
            }

            $operationMaterial = q3wOperationMaterial::find($inputMaterial->id);
            if (!isset($operationMaterial)) {
                $moveToConflict = true;

                if (empty($inputMaterial->comment)) {
                    $inputMaterialComment = null;
                } else {
                    $inputMaterialComment = $inputMaterial->comment;
                }

                if (!empty($inputMaterialComment)) {
                    $materialComment = new q3wOperationMaterialComment([
                        'comment' => $inputMaterialComment,
                        'author_id' => Auth::id()
                    ]);
                    $materialComment->save();
                    $materialCommentId = $materialComment->id;
                } else {
                    $materialCommentId = null;
                }

                $operationMaterial = new q3wOperationMaterial([
                        'material_operation_id' => $operation->id,
                        'standard_id' => $materialStandard->id,
                        'amount' => $inputMaterial->amount,
                        'quantity' => $inputMaterial->quantity,
                        'edit_states' => json_encode($inputMaterial->edit_states),
                        'initial_comment_id' => $inputMaterial->initial_comment_id,
                        'comment_id' => $materialCommentId
                    ]
                );
            } else {
                $operationMaterialComment = q3wOperationMaterialComment::find($inputMaterial->comment_id);

                if (isset($operationMaterialComment)) {
                    if ($operationMaterialComment->comment == $inputMaterial->comment) {
                        $materialCommentId = $inputMaterial->comment_id;
                    } else {
                        if (!empty($inputMaterial->comment)) {
                            $materialComment = new q3wOperationMaterialComment([
                                'comment' => $inputMaterial->comment,
                                'author_id' => Auth::id()
                            ]);
                            $materialComment->save();
                            $materialCommentId = $materialComment->id;
                        } else {
                            $materialCommentId = null;
                        }
                    }
                } else {
                    if (!empty($inputMaterial->comment)) {
                        $materialComment = new q3wOperationMaterialComment([
                            'comment' => $inputMaterial->comment,
                            'author_id' => Auth::id()
                        ]);
                        $materialComment->save();
                        $materialCommentId = $materialComment->id;
                    } else {
                        $materialCommentId = null;
                    }
                }

                $operationMaterial->amount = $inputMaterial->amount;
                $operationMaterial->quantity = $inputMaterial->quantity;
                $operationMaterial->edit_states = json_encode($inputMaterial->edit_states);
                $operationMaterial->comment_id = $materialCommentId;
            }
            $operationMaterial->save();

            if (!$moveToConflict) {
                if ($operationMaterial->initial_amount != $operationMaterial->amount ||
                    $operationMaterial->initial_quantity != $operationMaterial->quantity) {
                    $moveToConflict = true;
                }
            }
        }

        if (isset($requestData->new_comment)){
            $newComment = $requestData->new_comment;
        } else {
            $newComment = self::EMPTY_COMMENT_TEXT;
        }

        $materialOperationComment = new q3wOperationComment([
            'material_operation_id' => $operation->id,
            'operation_route_stage_id' => $operation->operation_route_stage_id,
            'comment' => $newComment,
            'user_id' => Auth::id()
        ]);

        $materialOperationComment->save();

        DB::commit();

        if (in_array($operation->operation_route_stage_id, [11, 19, 30, 38])) {
            if (isset($requestData->userAction)) {
                if ($requestData->userAction == "forceComplete") {
                    $moveToConflict = false;
                }
                if ($requestData->userAction == "moveToResponsibilityUser") {
                    $moveToConflict = true;
                }
            }
        }

        $this->moveOperationToNextStage($operation->id, $moveToConflict);
    }

    /**
     * @param $projectObjectId
     * @return bool
     */
    public function isUserResponsibleForMaterialAccounting(int $projectObjectId): bool
    {
        return ObjectResponsibleUser::where('user_id', Auth::id())
            ->where('object_id', $projectObjectId)->exists();
    }

    public function allowEditing(q3wMaterialOperation $operation): bool
    {
        switch ($operation->operation_route_stage_id) {
            case 6:
                return Auth::id() == $operation->destination_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 11:
                return Auth::id() == $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 19:
                return $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 25:
                return Auth::id() == $operation->source_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 30:
                return Auth::id() == $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 38:
                return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            default:
                return false;
        }
    }

    public function allowCancelling(q3wMaterialOperation $operation): bool
    {
        switch ($operation->operation_route_stage_id) {
            case 6:
                return Auth::id() == $operation->destination_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 11:
                return Auth::id() == $operation->destination_responsible_user_id || Auth::id() == $operation->source_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 19:
                return $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 25:
                return Auth::id() == $operation->destination_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 30:
                return Auth::id() == $operation->destination_responsible_user_id || Auth::id() == $operation->source_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 38:
                return $this->isUserResponsibleForMaterialAccounting($operation->source_responsible_user_id);
            default:
                return false;
        }
    }

    public function allowMoving(q3wMaterialOperation $operation): bool
    {
        switch ($operation->operation_route_stage_id) {
            case 6:
                return Auth::id() == $operation->destination_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 11:
                return Auth::id() == $operation->source_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 19:
                return $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 25:
                return Auth::id() == $operation->source_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 30:
                return Auth::id() == $operation->destination_responsible_user_id || $this->isUserResponsibleForMaterialAccounting($operation->destination_project_object_id);
            case 38:
                return $this->isUserResponsibleForMaterialAccounting($operation->source_responsible_user_id);
            default:
                return false;
        }
    }

    /**
     * Валидирует список материалов для операции
     * Материал считается валидным для перемещения если:
     *      1) Существует переданная в запросе заявка (operationId)
     *      2) Существует объект отправления (sourceProjectObjectId)
     *      3) На объекте сущесвует
     *          3.1) Для шпунта: Эталон + равное количество в единицах измерения (Пример: Шпунт VL 606A 14.5 м.п.)
     *          3.2) Для остального: Эталон
     *      4) Количество материала (в сумме по эталону или эталону + ед. изм.) на объекте отправления больше или равно, чем количество в заявке
     *      5) Отстатки не будут конфликтовать по количеству с ранее созданными заявками (Тут вопрос, нужно обсудить этот функционал) TODO: Реализовать проверку на конфликт заявок
     * Дополнительно информировать:
     *      1) Если длина материала в ед. изм. >= 15 м.п. (Только для м.п., для других единиц измерения это условие не нужно)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateMaterialList(Request $request)
    {
        $errors = [];

        $validationData = json_decode($request->getContent(), false);

        if (empty($validationData->operationId)) {
            $operationId = 0;
        } else {
            $operationId = $validationData->operationId;
        }

        if (empty($validationData->userAction)) {
            $userAction = "";
        } else {
            $userAction = $validationData->userAction;
        }

        if (empty($validationData->materials)) {
            $errors['common']['errorList'][] = (object)['severity' => 1000, 'type' => 'materialsNotFound', 'message' => 'Материалы не указаны'];
        } else {
            $materials = $validationData->materials;
        }

        $projectObject = ProjectObject::find($validationData->sourceProjectObjectId);
        if (empty($projectObject)) {
            $errors['common']['errorList'][] = (object)['severity' => 1000, 'type' => 'sourceProjectObjectNotFound', 'message' => 'Объект отправления не найден'];
        }

        $checkSourceObjectMaterialsCount = true;

        if (!empty($operationId)){
            $operation = q3wMaterialOperation::find($operationId);
            if (isset($operation)) {
                if ($userAction == "moveToResponsibilityUser") {
                    $checkSourceObjectMaterialsCount = false;
                } else {
                    $checkSourceObjectMaterialsCount = in_array($operation->operation_route_stage_id, [11, 19, 25, 38]);
                }
            }
        }

        if (!empty($materials)) {
            $unitedMaterials = [];

            foreach ($materials as $material) {
                if (isset($material->edit_states) && in_array("deletedByRecipient", $material->edit_states)) {
                    continue;
                }

                $materialStandard = q3wMaterialStandard::find($material->standard_id);

                $accountingType = $materialStandard->materialType->accounting_type;

                $key = $material->validationUid;

                switch ($accountingType) {
                    case 2:
                        if (array_key_exists($key, $unitedMaterials)) {
                            $unitedMaterials[$key]->amount = $unitedMaterials[$key]->amount + $material->amount;
                        } else {
                            $unitedMaterials[$key] = $material;
                            $unitedMaterials[$key]->max_quantity = $material->quantity;
                        }
                        break;
                    default:
                        if (array_key_exists($key, $unitedMaterials)) {
                            if ($unitedMaterials[$key]->max_quantity < $material->quantity){
                                $unitedMaterials[$key]->max_quantity = $material->quantity;
                            }
                            $unitedMaterials[$key]->quantity = $unitedMaterials[$key]->quantity + $material->quantity * $material->amount;
                            $unitedMaterials[$key]->amount = 1;
                        } else {
                            $unitedMaterials[$key] = $material;
                            $unitedMaterials[$key]->max_quantity = $material->quantity;
                            $unitedMaterials[$key]->quantity = $material->quantity * $material->amount;
                            $unitedMaterials[$key]->amount = 1;
                        }
                }

                if (!isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                    $errors[$key]['errorList'][] = (object)['severity' => 1000, 'type' => 'amountIsNull', 'itemName' => $materialStandard->name, 'message' => 'Количество в штуках не указано'];
                }

                if (!isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                    $errors[$key]['errorList'][] = (object)['severity' => 1000, 'type' => 'quantityIsNull', 'itemName' => $materialStandard->name, 'message' => 'Количество в единицах измерения не указано'];
                }
            }

            $totalWeight = 0;

            foreach ($unitedMaterials as $key => $unitedMaterial) {
                $materialStandard = q3wMaterialStandard::find($unitedMaterial->standard_id);

                $accountingType = $materialStandard->materialType->accounting_type;

                $materialName = $materialStandard->name;

                if (isset($errors[$key]) && count($errors[$key]) > 0) {
                    $errors[$key]['isValid'] = false;
                    continue;
                }

                $sourceProjectObjectMaterial = (new q3wMaterial)
                    ->where('project_object', $projectObject->id)
                    ->where('standard_id', $unitedMaterial->standard_id)
                    ->where(function ($query) use ($accountingType, $unitedMaterial) {
                        switch ($accountingType) {
                            case 2:
                                $query->where('quantity', '=',  $unitedMaterial->quantity);
                                break;
                        }
                    })
                    ->where(function ($query) use ($unitedMaterial) {
                        if (empty($unitedMaterial->initial_comment_id)){
                            $query->whereNull('comment_id');
                        } else {
                            $query->where('comment_id', $unitedMaterial->initial_comment_id);
                        }
                    })
                    ->get()
                    ->first();

                $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $unitedMaterial->standard_id)
                    ->where('material_operation_id', '<>', $operationId)
                    ->where('initial_comment_id', $unitedMaterial->initial_comment_id)
                    ->where(function ($query) use ($accountingType, $unitedMaterial) {
                        switch ($accountingType) {
                            case 2:
                                $query->where('quantity', '=',  $unitedMaterial->quantity);
                                break;
                        }
                    })
                    ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                    ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                    ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                    ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                    ->where('q3w_material_operations.source_project_object_id', $projectObject->id)
                    ->get(DB::raw('sum(`amount`) as amount, sum(`quantity`) as quantity'))
                    ->first();

                $operationAmount = $activeOperationMaterialAmount->amount;
                $operationQuantity = $activeOperationMaterialAmount->quantity;

                if (!isset($errors[$key])) {
                    if ($materialStandard->materialType->measure_unit == 1) {
                        if ($unitedMaterial->max_quantity > 15) {
                            $errors[$key]['errorList'][] = (object)['severity' => 500, 'type' => 'largeMaterialLength', 'itemName' => $materialName, 'message' => 'Габарит груза превышает 15 м.п.'];
                        }
                    }
                }

                if ($checkSourceObjectMaterialsCount) {
                    if (!isset($sourceProjectObjectMaterial)) {
                        $errors[$key]['errorList'][] = (object)['severity' => 1000, 'type' => 'materialNotFound', 'itemName' => $materialName, 'message' => 'На объекте отправления не существует такого материала'];
                    } else {
                        if ($accountingType == 2) {
                            $materialAmountDelta = $sourceProjectObjectMaterial->amount - $unitedMaterial->amount - $operationAmount;
                        } else {
                            $materialAmountDelta = $sourceProjectObjectMaterial->quantity - $unitedMaterial->quantity - $operationQuantity * $operationAmount;
                        }

                        if ($materialAmountDelta < 0) {
                            $errors[$key]['errorList'][] = (object)['severity' => 1000, 'type' => 'negativeMaterialQuantity', 'itemName' => $materialName, 'message' => 'На объекте отправления недостаточно материала'];
                        }
                    }
                }

                if (isset($errors[$key]['errorList'])) {
                    $errors[$key]['isValid'] = false;
                } else {
                    $errors[$key]['isValid'] = true;
                }

                $totalWeight += $unitedMaterial->amount * $unitedMaterial->quantity * $materialStandard->weight;
            }

            if ($totalWeight > 20) {
                $errors['common']['errorList'][] = (object)['severity' => 500, 'type' => 'totalWeightTooLarge', 'message' => 'Общая масса материалов превышает 20 т.'];
            }
        }

        $errorResult = [];

        foreach ($errors as $key => $error){
            if ($key != "common") {
                if ($error['isValid']) {
                    $errorResult[] = ['validationUid' => $key, 'isValid' => $error['isValid']];
                } else {
                    $errorResult[] = ['validationUid' => $key, 'isValid' => $error['isValid'], 'errorList' => $error['errorList']];
                }
            } else {
                $errorResult[] = ['validationUid' => $key, 'isValid' => false, 'errorList' => $error['errorList']];
            }
        }

        return response()->json([
            'validationResult' => $errorResult,
            'timestamp' => $validationData->timestamp
        ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public function cancelOperation(Request $request){
        $requestData = json_decode($request["data"]);

        $operation = q3wMaterialOperation::findOrFail($requestData->operationId);
        if ($this->allowCancelling($operation)){
            DB::beginTransaction();

            if (isset($requestData->new_comment)) {
                $materialOperationComment = new q3wOperationComment([
                    'material_operation_id' => $operation->id,
                    'operation_route_stage_id' => $operation->operation_route_stage_id,
                    'comment' => $requestData->new_comment,
                    'user_id' => Auth::id()
                ]);

                $materialOperationComment->save();
            }

            DB::commit();

            $this->moveOperationToNextStage($operation->id, false, true);
        }
    }

    public function sendTransferNotificationToResponsibilityUsersOfObject(q3wMaterialOperation $operation, string $notificationText, int $projectObjectId) {
        $responsibilityUsers = ObjectResponsibleUser::where('object_id', $projectObjectId)->get();

        foreach ($responsibilityUsers as $responsibilityUser) {
            $this->sendTransferNotification($operation, $notificationText, $responsibilityUser->user_id, $projectObjectId);
        }
    }

    public function sendTransferNotification(q3wMaterialOperation $operation, string $notificationText, int $notifiedUserId, int $projectObjectId){
        $sourceProjectObject = ProjectObject::where('id', $operation->source_project_object_id)->first();
        $destinationProjectObject = ProjectObject::where('id', $operation->destination_project_object_id)->first();

        $notificationText = 'Операция #' . $operation->id . ' от ' . $operation->created_at->format('d.m.Y') . PHP_EOL . PHP_EOL . $sourceProjectObject->short_name . ' ➡️ ' . $destinationProjectObject->short_name . PHP_EOL . PHP_EOL . $notificationText;

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = PHP_EOL . route('materials.operations.transfer.view') . '?operationId=' . $operation->id;
        $notification->update([
            'name' => $notificationText,
            'target_id' => $operation->id,
            'user_id' => $notifiedUserId,
            'object_id' => $projectObjectId,
            'created_at' => now(),
            'type' => 7,
            'status' => 7
        ]);
    }

    public function completed(Request $request)
    {
        $operation = q3wMaterialOperation::leftJoin('project_objects as source_project_objects', 'source_project_objects.id', '=', 'q3w_material_operations.source_project_object_id')
            ->leftJoin('project_objects as destination_project_objects', 'destination_project_objects.id', '=', 'q3w_material_operations.destination_project_object_id')
            ->leftJoin('users as destination_users', 'destination_users.id', '=', 'q3w_material_operations.destination_responsible_user_id')
            ->leftJoin('users as source_users', 'source_users.id', '=', 'q3w_material_operations.source_responsible_user_id')
            ->get(['q3w_material_operations.*',
                'source_project_objects.short_name as source_project_object_name',
                'destination_project_objects.short_name as destination_project_object_name',
                DB::Raw('CONCAT(`destination_users`.`last_name`, " ", UPPER(SUBSTRING(`destination_users`.`first_name`, 1, 1)), ". ", UPPER(SUBSTRING(`destination_users`.`patronymic`, 1, 1)), ".") as destination_responsible_user_name'),
                DB::Raw('CONCAT(`source_users`.`last_name`, " ", UPPER(SUBSTRING(`source_users`.`first_name`, 1, 1)), ". ", UPPER(SUBSTRING(`source_users`.`patronymic`, 1, 1)), ".") as source_responsible_user_name')
            ])
            ->where('id', '=', $request->operationId)
            ->first();

        if (!isset($operation)) {
            abort(404, 'Операция не найдена');
        }

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);
        $operationRouteStage = q3wOperationRouteStage::find($operation->operation_route_stage_id)->name;

        $materials = DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f', 'a.material_operation_id', '=', 'f.id')
            ->leftJoin('q3w_materials as g', 'a.standard_id', '=', 'g.standard_id')
            ->leftJoin('q3w_operation_material_comments as j', 'a.comment_id', '=', 'j.id')
            ->where('a.material_operation_id', '=', $operation->id)
            ->distinct()
            ->get(['a.id',
                'a.standard_id',
                'a.amount',
                'a.quantity',
                'a.edit_states',
                'b.name as standard_name',
                'b.material_type',
                'b.weight as standard_weight',
                'd.accounting_type',
                'd.measure_unit',
                'e.value as measure_unit_value',
                'j.comment'
            ])
            ->toJson(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        return view('materials.operations.transfer.completed')->with([
            'operationData' => $operationData,
            'operationMaterials' => $materials,
            'operationRouteStage' => $operationRouteStage,
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE)
        ]);
    }
}
