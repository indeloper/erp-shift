<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialComment;
use App\Models\q3wMaterial\q3wMaterialSnapshot;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\q3wMaterial\q3wOperationMaterialComment;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Building\ObjectResponsibleUserRole;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class q3wMaterialTransformationOperationController extends Controller
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
        if (isset($request->projectObjectId)) {
            $projectObjectId = $request->projectObjectId;
        } else {
            $projectObjectId = 0;
        }

        return view('materials.operations.transformation.new')->with([
            'projectObjectId' => $projectObjectId,
            'currentUserId' => Auth::id(),
            'measureUnits' => q3wMeasureUnit::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => DB::table('q3w_material_standards as a')
                ->leftJoin('q3w_material_types as b', 'a.material_type', '=', 'b.id')
                ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
                ->get(['a.*', 'b.name as material_type_name', 'b.measure_unit', 'b.accounting_type', 'd.value as measure_unit_value'])
                ->toJSON(),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name')->toJson(JSON_UNESCAPED_UNICODE),
            'users' => User::getAllUsers()->where('status', 1)->get()->toJson(JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function view(Request $request)
    {
        if (isset($request->operationId)) {
            $operation = q3wMaterialOperation::findOrFail($request->operationId);
        }

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);
        $operationRouteStage = q3wOperationRouteStage::find($operation->operation_route_stage_id)->name;

        return view('materials.operations.transformation.view')->with([
            'operationData' => $operationData,
            'operationRouteStage' => $operationRouteStage,
            'currentUserId' => Auth::id(),
            'measureUnits' => q3wMeasureUnit::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => DB::table('q3w_material_standards as a')
                ->leftJoin('q3w_material_types as b', 'a.material_type', '=', 'b.id')
                ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
                ->get(['a.*', 'b.name as material_type_name', 'b.measure_unit', 'b.accounting_type', 'd.value as measure_unit_value'])
                ->toJSON(),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name')->toJson(JSON_UNESCAPED_UNICODE),
            'users' => User::getAllUsers()
                ->where('status', 1)
                ->get()
                ->toJson(JSON_UNESCAPED_UNICODE),
            'operationMaterials' => q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
                ->leftJoin('q3w_material_standards as b', 'q3w_operation_materials.standard_id', '=', 'b.id')
                ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
                ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
                ->leftJoin('q3w_material_comments as f', 'q3w_operation_materials.initial_comment_id', '=', 'f.id')
                ->leftJoin('q3w_operation_material_comments as g', 'q3w_operation_materials.comment_id', '=', 'g.id')
                ->get(['q3w_operation_materials.*',
                    'b.name as standard_name',
                    'd.measure_unit',
                    'd.name as material_type_name',
                    'd.accounting_type',
                    'e.value as measure_unit_value',
                    'q3w_operation_materials.comment_id',
                    'q3w_operation_materials.initial_comment_id',
                    'g.comment',
                    'f.comment as initial_comment'])
                ->toJson(JSON_UNESCAPED_UNICODE),
            'allowEditing' => $this->allowEditing($operation),
            'allowCancelling' => $this->allowCancelling($operation),
            'routeStageId' => $operation->operation_route_stage_id
        ]);
    }

    function validateStage(int $projectObjectId, String $stage, Array $materialsToTransform, Array $materialsAfterTransform = null) {
        $errors = [];
        if ($stage == 'fillingMaterialsToTransform') {
            foreach ($materialsToTransform as $material) {
                $material = (object)$material;
                $materialStandard = q3wMaterialStandard::find($material->standard_id);

                if (!isset($materialStandard)) {
                    $errors['common'][] = (object)['severity' => 1000, 'type' => 'materialStandardNotFound', 'message' => 'Эталона материала с идентификатором "' . $material->standard_id . '" не существует'];
                    continue;
                }

                $accountingType = $materialStandard->materialType->accounting_type;

                switch ($accountingType) {
                    case 2:
                        $key = $material->standard_id . '-' . $material->quantity;
                        break;
                    default:
                        $key = $material->standard_id;
                }

                $materialName = $materialStandard->name;

                if (!isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'amountIsNull', 'itemName' => $materialName, 'message' => 'Количество в штуках не указано'];
                }

                if (!isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'quantityIsNull', 'itemName' => $materialName, 'message' => 'Количество в единицах измерения не указано'];
                }

                if (isset($errors[$key]) && count($errors[$key]) > 0) {
                    continue;
                }

                if ($accountingType == 2) {
                    $projectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObjectId)
                        ->where('standard_id', $material->standard_id)
                        ->where('quantity', $material->quantity)
                        ->where('comment_id', $material->initial_comment_id)
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                        ->where('quantity', $material->quantity)
                        ->where('initial_comment_id', $material->initial_comment_id)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
                        ->where('q3w_material_operations.source_project_object_id', $projectObjectId)
                        ->get(DB::raw('sum(`amount`) as amount'))
                        ->first();

                    $operationAmount = $activeOperationMaterialAmount->amount;
                } else {
                    $projectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObjectId)
                        ->where('standard_id', $material->standard_id)
                        ->where('comment_id', $material->initial_comment_id)
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->where('initial_comment_id', $material->initial_comment_id)
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
                        ->where('q3w_material_operations.source_project_object_id', $projectObjectId)
                        ->get(DB::raw('sum(`amount`) as amount, sum(`quantity`) as quantity'))
                        ->first();;

                    $operationAmount = $activeOperationMaterialAmount->amount;
                    $operationQuantity = $activeOperationMaterialAmount->quantity;
                }

                if (!isset($projectObjectMaterial)) {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'materialNotFound', 'itemName' => $materialName, 'message' => 'На объекте отправления не существует такого материала'];
                } else {
                    if ($accountingType == 2) {
                        $materialAmountDelta = $projectObjectMaterial->amount - $material->amount - $operationAmount;
                    } else {
                        $materialAmountDelta = $projectObjectMaterial->quantity - $material->quantity * $material->amount - $operationQuantity * $operationAmount;
                    }

                    if ($materialAmountDelta < 0) {
                        $errors[$key][] = (object)['severity' => 1000, 'type' => 'negativeMaterialQuantity', 'itemName' => $materialName, 'message' => 'На объекте недостаточно материала'];
                    }
                }

            }
            return $errors;
        }

        if ($stage == 'fillingMaterialsAfterTransform') {
            $unitedMaterialToTransform = [];
            $totalSourceQuantity = 0;
            $totalQuantity = 0;

            foreach ($materialsToTransform as $material){
                $material = (object)$material;
                $materialStandard = q3wMaterialStandard::find($material->standard_id);

                if (!isset($materialStandard)) {
                    $errors['common'][] = (object)['severity' => 1000, 'type' => 'materialStandardNotFound', 'message' => 'Эталона материала с идентификатором "' . $material->standard_id . '" не существует'];
                    continue;
                }

                $accountingType = $materialStandard->materialType->accounting_type;

                switch ($accountingType) {
                    case 2:
                        $key = $material->standard_id . '-' . $material->quantity;
                        break;
                    default:
                        $key = $material->standard_id;
                }

                if (isset($unitedMaterialToTransform[$key])){
                    $unitedMaterialToTransform[$key] = $unitedMaterialToTransform[$key] + $material->quantity * $material->amount;
                } else {
                    $unitedMaterialToTransform[$key] = $material->quantity * $material->amount;
                }

                $totalSourceQuantity += round($material->quantity * $material->amount, 2);
            }



            foreach ($materialsAfterTransform as $material) {
                $material = (object)$material;
                $materialStandard = q3wMaterialStandard::find($material->standard_id);

                if (!isset($materialStandard)) {
                    $errors['common'][] = (object)['severity' => 1000, 'type' => 'materialStandardNotFound', 'message' => 'Эталона материала с идентификатором "' . $material->standard_id . '" не существует'];
                    continue;
                }

                $accountingType = $materialStandard->materialType->accounting_type;

                switch ($accountingType) {
                    case 2:
                        $key = $material->standard_id . '-' . $material->quantity;
                        break;
                    default:
                        $key = $material->standard_id;
                }

                $materialName = $materialStandard->name;

                if (!isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'amountIsNull', 'itemName' => $materialName, 'message' => 'Количество в штуках не указано'];
                }

                if (!isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'quantityIsNull', 'itemName' => $materialName, 'message' => 'Количество в единицах измерения не указано'];
                }

                if (isset($errors[$key]) && count($errors[$key]) > 0) {
                    continue;
                }

                $totalQuantity += round($material->quantity * $material->amount, 2);

            }

            if ($totalQuantity > $totalSourceQuantity) {
                $errors[$key][] = (object)['severity' => 1000, 'type' => 'totalQuantityIsLarge', 'itemName' => $materialName, 'message' => 'Длина материалов после преобразования больше, чем длина исходных материалов'];
            }

            return $errors;
        }
    }

    public function validateMaterialList(Request $request)
    {

        $errors = [];

        $projectObject = ProjectObject::find($request->transformationOperationData['projectObjectId']);
        if (!isset($projectObject)) {
            $errors['common'][] = (object)['severity' => 1000, 'type' => 'projectObjectNotFound', 'message' => 'Объект не найден'];
        }

        switch($request->transformationOperationData['transformationStage']) {
            case 'fillingMaterialsToTransform':
                if (isset($request->transformationOperationData['transformationStage'])) {
                    $errors = array_merge($errors,
                        $this->validateStage($projectObject->id,
                            'fillingMaterialsToTransform',
                            $request->transformationOperationData['materialsToTransform']
                        )
                    );
                } else {
                    $errors['common'][] = (object)['severity' => 1000, 'type' => 'materialsNotFound', 'message' => 'Этап провеки не указан'];
                }
                break;
            case 'fillingMaterialsAfterTransform':
                if (isset($request->transformationOperationData['transformationStage'])) {
                    $errors = array_merge($errors,
                        $this->validateStage($projectObject->id,
                            'fillingMaterialsAfterTransform',
                            $request->transformationOperationData['materialsToTransform'],
                            $request->transformationOperationData['materialsAfterTransform']
                        )
                    );
                } else {
                    $errors['common'][] = (object)['severity' => 1000, 'type' => 'materialsNotFound', 'message' => 'Этап провеки не указан'];
                }
                break;
        }

        $errorResult = [];

        foreach ($errors as $key => $error){
            $errorResult[] = ['validationId' => $key, 'errorList' => $error];
        }

        if (count($errors) == 0) {
            return response()->json([
                'result' => 'ok'
            ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'result' => 'error',
                'errors' => $errorResult
            ], 400, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
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

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 3,
            'operation_route_stage_id' => 69,
            'source_project_object_id' => $requestData['project_object_id'],
            'operation_date' => $requestData['operation_date'],
            'creator_user_id' => Auth::id(),
            'source_responsible_user_id' => $requestData['responsible_user_id'],
            'transformation_type_id' => $requestData['transformation_type_id']
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

        foreach ($requestData['materialsToTransform'] as $material) {
            $materialStandard = q3wMaterialStandard::findOrFail($material['standard_id']);

            $materialAmount = $material['amount'];
            $materialQuantity = $material['quantity'];

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $materialAmount,
                'quantity' => $materialQuantity,
                'initial_amount' => $materialAmount,
                'initial_quantity' => $materialQuantity,
                'transform_operation_stage_id' => 1,
                'initial_comment_id' => $material['initial_comment_id']
            ]);

            $operationMaterial->save();
        }

        foreach ($requestData['materialsAfterTransform'] as $material) {
            $materialStandard = q3wMaterialStandard::findOrFail($material['standard_id']);

            $materialAmount = $material['amount'];
            $materialQuantity = $material['quantity'];

            if (empty($material['comment'])) {
                $inputMaterialComment = null;
            } else {
                $inputMaterialComment = $material['comment'];
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
                'amount' => $materialAmount,
                'quantity' => $materialQuantity,
                'initial_amount' => $materialAmount,
                'initial_quantity' => $materialQuantity,
                'transform_operation_stage_id' => 2,
                'comment_id' => $materialCommentId
            ]);

            $operationMaterial->save();
        }

        foreach ($requestData['materialsRemains'] as $material) {
            $materialStandard = q3wMaterialStandard::findOrFail($material['standard_id']);

            $materialAmount = $material['amount'] ?? 0;
            $materialQuantity = $material['quantity'] ?? 0;

            if (empty($material['comment'])) {
                $inputMaterialComment = null;
            } else {
                $inputMaterialComment = $material['comment'];
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
                'amount' => $materialAmount,
                'quantity' => $materialQuantity,
                'initial_amount' => $materialAmount,
                'initial_quantity' => $materialQuantity,
                'transform_operation_stage_id' => 3,
                'comment_id' => $materialCommentId
            ]);

            $operationMaterial->save();
        }


        $materialOperation->operation_route_stage_id = 70;
        $materialOperation->save();

        if (!$this->isUserResponsibleForMaterialAccounting($materialOperation->source_project_object_id)) {
            $this->sendTransformationNotificationToResponsibilityUsersOfObject($materialOperation, 'Ожидание согласования преобразования', $materialOperation->source_project_object_id);
        }

        $materialOperation->operation_route_stage_id = 71;
        $materialOperation->save();

        if ($this->isUserResponsibleForMaterialAccounting($materialOperation->source_project_object_id)) {
            $this->move($materialOperation);
            (new q3wMaterialSnapshot)->takeSnapshot($materialOperation, ProjectObject::find($materialOperation->source_project_object_id));

            $materialOperation->operation_route_stage_id = 73;
            $materialOperation->save();
        }

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function sendTransformationNotificationToResponsibilityUsersOfObject(q3wMaterialOperation $operation, string $notificationText, int $projectObjectId) {

        $responsibilityUsers = (new ObjectResponsibleUser)->getResponsibilityUsers($projectObjectId, $role='TONGUE_PROJECT_MANAGER');

        foreach ($responsibilityUsers as $responsibilityUser) {
            $this->sendTransformationNotification($operation, $notificationText, $responsibilityUser->user_id, $projectObjectId);
        }
    }

    public function sendTransformationNotification(q3wMaterialOperation $operation, string $notificationText, int $notifiedUserId, int $projectObjectId){
        $sourceProjectObject = ProjectObject::where('id', $operation->source_project_object_id)->first();

        $notificationText = 'Операция #' .
            $operation->id .
            ' от ' .
            $operation->created_at->format('d.m.Y') .
            PHP_EOL .
            PHP_EOL .
            $sourceProjectObject->short_name .
            PHP_EOL .
            PHP_EOL .
            $notificationText;

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = PHP_EOL . $operation->url;
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

    public function move(q3wMaterialOperation $operation)
    {
        $materialsToTransfer = q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
            ->where('transform_operation_stage_id', '=', 1)->get()->toArray();

        foreach ($materialsToTransfer as $materialToTransfer) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialToTransfer['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialToTransfer['amount'];
            $materialQuantity = $materialToTransfer['quantity'];

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $materialQuantity)
                    ->where(function ($query) use ($materialToTransfer) {
                        if (empty($materialToTransfer['initial_comment_id'])) {
                            $query->whereNull('comment_id');
                        } else {
                            $query->where('comment_id', $materialToTransfer['initial_comment_id']);
                        }
                    })
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->first();
            }

            if (!isset($material)) {
                abort(400, 'Source material not found');
            }

            if ($materialType->accounting_type == 2) {
                $material->amount = $material->amount - $materialAmount;
            } else {
                $material->amount = 1;
                $material->quantity = $material->quantity - $materialQuantity * $materialAmount;
            }

            if ( $material->amount < 0){
                abort(400, 'Negative material amount after transformation');
            }

            if ( $material->quantity < 0){
                abort(400, 'Negative quantity after transformation');
            }

            $material->save();
        }

        $materialsAfterTransfer = q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
            ->where('transform_operation_stage_id', '=', 2)->get()->toArray();

        foreach($materialsAfterTransfer as $materialAfterTransfer) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialAfterTransfer['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialAfterTransfer['amount'];
            $materialQuantity = $materialAfterTransfer['quantity'];

            if (empty($materialAfterTransfer['comment_id'])) {
                $materialAfterTransferComment = null;
            } else {
                $materialAfterTransferComment = q3wOperationMaterialComment::find($materialAfterTransfer['comment_id'])->comment;
            }

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->leftJoin('q3w_material_comments', 'comment_id', '=', 'q3w_material_comments.id')
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $materialQuantity)
                    ->where(function ($query) use ($materialAfterTransferComment) {
                        if (!empty($materialAfterTransferComment)) {
                            $query->where('comment', 'like', $materialAfterTransferComment);
                        } else {
                            $query->whereNull('comment_id');
                        }
                    })
                    ->get('q3w_materials.*')
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->get('q3w_materials.*')
                    ->first();
            }

            if (isset($material)) {
                if ($materialType->accounting_type == 2) {
                    $material->amount = $material->amount + $materialAmount;
                } else {
                    $material->amount = 1;
                    $material->quantity = $material->quantity + $materialQuantity * $materialAmount;
                }
            } else {
                if (!empty($materialAfterTransferComment)) {
                    $materialComment = new q3wMaterialComment([
                        'comment' => $materialAfterTransferComment,
                        'author_id' => Auth::id()
                    ]);
                    $materialComment->save();
                    $materialCommentId = $materialComment->id;
                } else {
                    $materialCommentId = null;
                };

                $material = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $operation->source_project_object_id,
                    'amount' => $materialAmount,
                    'quantity' => $materialQuantity,
                    'comment_id' => $materialCommentId
                ]);
            }
            $material->save();
        }

        $materialsRemains = q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
            ->where('transform_operation_stage_id', '=', 3)->get()->toArray();

        foreach($materialsRemains as $materialRemains) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialRemains['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialRemains['amount'];
            $materialQuantity = $materialRemains['quantity'];

            if (empty($materialRemains['comment_id'])) {
                $materialRemainsComment = null;
            } else {
                $materialRemainsComment = q3wOperationMaterialComment::find($materialRemains['comment_id'])->comment;
            }

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->leftJoin('q3w_material_comments', 'comment_id', '=', 'q3w_material_comments.id')
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $materialQuantity)
                    ->where(function ($query) use ($materialRemainsComment) {
                        if (!empty($materialRemainsComment)) {
                            $query->where('comment', 'like', $materialRemainsComment);
                        } else {
                            $query->whereNull('comment_id');
                        }
                    })
                    ->get('q3w_materials.*')
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->first();
            }

            if (isset($material)) {
                if ($materialType->accounting_type == 2) {
                    $material->amount = $material->amount + $materialAmount;
                } else {
                    $material->amount = 1;
                    $material->quantity = $material->quantity + $materialQuantity * $materialAmount;
                }

            } else {
                if (!empty($materialRemainsComment)) {
                    $materialComment = new q3wMaterialComment([
                        'comment' => $materialRemainsComment,
                        'author_id' => Auth::id()
                    ]);
                    $materialComment->save();
                    $materialCommentId = $materialComment->id;
                } else {
                    $materialCommentId = null;
                };

                $material = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $operation->source_project_object_id,
                    'amount' => $materialAmount,
                    'quantity' => $materialQuantity,
                    'comment_id' => $materialCommentId
                ]);
            }
            $material->save();
        }
    }

    public function confirmOperation(Request $request)
    {
        $requestData = json_decode($request["data"]);

        $operation = q3wMaterialOperation::findOrFail($requestData->operationId);
        if (!$this->allowEditing($operation)) {
            abort(400, 'You are in read-only mode');
        }

        $projectObject = ProjectObject::findOrFail($operation->source_project_object_id);

        DB::beginTransaction();

        $this->move($operation);

        $commentText = $requestData->new_comment ?? self::EMPTY_COMMENT_TEXT;

        $materialOperationComment = new q3wOperationComment([
            'material_operation_id' => $operation->id,
            'operation_route_stage_id' => $operation->operation_route_stage_id,
            'comment' => $commentText,
            'user_id' => Auth::id()
        ]);

        $materialOperationComment->save();


        (new q3wMaterialSnapshot)->takeSnapshot($operation, $projectObject);

        $operation->operation_route_stage_id = 72;
        $operation->save();

        $this->sendTransformationNotification($operation,
            'Операция подтверждена руководителем',
            $operation->source_responsible_user_id,
            $operation->source_project_object_id
        );

        $operation->operation_route_stage_id = 73;
        $operation->save();

        $materialOperationComment->operation_route_stage_id = $operation->operation_route_stage_id;
        $materialOperationComment->save();

        DB::commit();
    }

    public function cancelOperation(Request $request){
        $requestData = json_decode($request["data"]);

        $operation = q3wMaterialOperation::findOrFail($requestData->operationId);
        if ($this->allowCancelling($operation)){
            DB::beginTransaction();

            if (isset($requestData->new_comment)) {
                $commentText = $requestData->new_comment;
            } else {
                $commentText = self::EMPTY_COMMENT_TEXT;
            }

            $materialOperationComment = new q3wOperationComment([
                'material_operation_id' => $operation->id,
                'operation_route_stage_id' => $operation->operation_route_stage_id,
                'comment' => $commentText,
                'user_id' => Auth::id()
            ]);

            $operation->operation_route_stage_id = 72;
            $operation->save();

            $this->sendTransformationNotification($operation,
                'Операция отменена руководителем',
                $operation->source_responsible_user_id,
                $operation->source_project_object_id
            );

            $operation->operation_route_stage_id = 74;
            $operation->save();

            $materialOperationComment->operation_route_stage_id = $operation->operation_route_stage_id;
            $materialOperationComment->save();

            DB::commit();
        }
    }

    public function completed(Request $request)
    {
        $operation = q3wMaterialOperation::leftJoin('project_objects', 'project_objects.id', '=', 'q3w_material_operations.source_project_object_id')
            ->leftJoin('users', 'users.id', '=', 'q3w_material_operations.source_responsible_user_id')
            ->leftJoin('q3w_material_transformation_types', 'q3w_material_transformation_types.id', '=', 'q3w_material_operations.transformation_type_id')
            ->get(['q3w_material_operations.*',
                'project_objects.short_name as source_project_object_name',
                'q3w_material_transformation_types.value as transformation_type_value',
                DB::Raw('CONCAT(`users`.`last_name`, " ", UPPER(SUBSTRING(`users`.`first_name`, 1, 1)), ". ", UPPER(SUBSTRING(`users`.`patronymic`, 1, 1)), ".") as source_responsible_user_name')
            ])
            ->where('id', '=', $request->operationId)
            ->first();

        if (!isset($operation)) {
            abort(404, 'Операция не найдена');
        }

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);
        $operationRouteStage = q3wOperationRouteStage::find($operation->operation_route_stage_id)->name;

        return view('materials.operations.transformation.completed')->with([
            'operationData' => $operationData,
            'operationRouteStage' => $operationRouteStage,
            'operationMaterials' => q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
                ->leftJoin('q3w_material_standards as b', 'q3w_operation_materials.standard_id', '=', 'b.id')
                ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
                ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
                ->leftJoin('q3w_material_comments as f', 'q3w_operation_materials.initial_comment_id', '=', 'f.id')
                ->leftJoin('q3w_operation_material_comments as g', 'q3w_operation_materials.comment_id', '=', 'g.id')
                ->get(['q3w_operation_materials.*',
                    'b.name as standard_name',
                    'd.measure_unit',
                    'd.name as material_type_name',
                    'd.accounting_type',
                    'e.value as measure_unit_value',
                    'q3w_operation_materials.comment_id',
                    'q3w_operation_materials.initial_comment_id',
                    'g.comment',
                    'f.comment as initial_comment'])
                ->toJson(JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function isUserResponsibleForMaterialAccounting(int $projectObjectId): bool
    {
        return (new ObjectResponsibleUser)->isUserResponsibleForObject(Auth::id(), $projectObjectId, $role='TONGUE_PROJECT_MANAGER');
    }

    public function allowEditing(q3wMaterialOperation $operation): bool
    {
        return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
    }

    public function allowCancelling(q3wMaterialOperation $operation): bool
    {
        return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
    }

    public function isUserResponsibleForMaterialAccountingWebRequest(Request $request) {
        $requestData = json_decode($request["data"]);
        return response()->json([
            'isUserResponsibleForMaterialAccounting' => $this->isUserResponsibleForMaterialAccounting($requestData->project_object_id)
        ]);
    }
}
