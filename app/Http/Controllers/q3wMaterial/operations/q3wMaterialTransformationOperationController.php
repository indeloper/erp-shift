<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
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
            'users' => User::getAllUsers()->where('status', 1)->get()->toJson(JSON_UNESCAPED_UNICODE)
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
                ->get(['q3w_operation_materials.*',
                    'b.name as standard_name',
                    'd.measure_unit',
                    'd.name as material_type_name',
                    'd.accounting_type',
                    'e.value as measure_unit_value'])
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
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                        ->where('quantity', $material->quantity)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->where('q3w_material_operations.source_project_object_id', $projectObjectId)
                        ->get(DB::raw('sum(`amount`) as amount'))
                        ->first();

                    $operationAmount = $activeOperationMaterialAmount->amount;
                } else {
                    $projectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObjectId)
                        ->where('standard_id', $material->standard_id)
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $material->standard_id)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
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

                $totalSourceQuantity += $material->quantity * $material->amount;
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

                $totalQuantity += $material->quantity * $material->amount;

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
            'source_responsible_user_id' => $requestData['responsible_user_id']
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
            $materialType = $materialStandard->materialType;

            $materialAmount = $material['amount'];
            $materialQuantity = $material['quantity'];

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $materialAmount,
                'quantity' => $materialQuantity,
                'initial_amount' => $materialAmount,
                'initial_quantity' => $materialQuantity,
                'transfer_operation_stage_id' => 1
            ]);

            $operationMaterial->save();
        }

        foreach ($requestData['materialsAfterTransform'] as $material) {
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
                'transfer_operation_stage_id' => 2
            ]);

            $operationMaterial->save();
        }

        foreach ($requestData['materialsRemains'] as $material) {
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
                'transfer_operation_stage_id' => 3
            ]);

            $operationMaterial->save();
        }

        $materialOperation->operation_route_stage_id = 70;
        $materialOperation->save();

        $this->sendTransformationNotificationToResponsibilityUsersOfObject($materialOperation, 'Ожидание согласования преобразования', $materialOperation->source_project_object_id);

        $materialOperation->operation_route_stage_id = 71;
        $materialOperation->save();

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function sendTransformationNotificationToResponsibilityUsersOfObject(q3wMaterialOperation $operation, string $notificationText, int $projectObjectId) {
        $responsibilityUsers = ObjectResponsibleUser::where('object_id', $projectObjectId)->get();

        foreach ($responsibilityUsers as $responsibilityUser) {
            $this->sendTransformationNotification($operation, $notificationText, $responsibilityUser->id, $projectObjectId);
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
            ->where('transfer_operation_stage_id', '=', 1)->get()->toArray();

        foreach ($materialsToTransfer as $materialToTransfer) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialToTransfer['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialToTransfer['amount'];
            $materialQuantity = $materialToTransfer['quantity'];

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $materialQuantity)
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
                abort(400, 'Negative quantity amount after transformation');
            }

            $material->save();
        }

        $materialsAfterTransfer = q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
            ->where('transfer_operation_stage_id', '=', 2)->get()->toArray();

        foreach($materialsAfterTransfer as $materialAfterTransfer) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialAfterTransfer['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialAfterTransfer['amount'];
            $materialQuantity = $materialAfterTransfer['quantity'];

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $materialQuantity)
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

                $material -> save();
            } else {
                $material = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $operation->source_project_object_id,
                    'amount' => $materialAmount,
                    'quantity' => $materialQuantity
                ]);

                if ($materialType->accounting_type == 2) {
                    $material->amount = $materialAmount;
                    $material->quantity = $materialQuantity;
                } else {
                    $material->amount = 1;
                    $material->quantity = $materialQuantity * $materialAmount;
                }

                $material->save();
            }
        }

        $materialsRemains = q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
            ->where('transfer_operation_stage_id', '=', 3)->get()->toArray();

        foreach($materialsRemains as $materialRemains) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialRemains['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialRemains['amount'];
            $materialQuantity = $materialRemains['quantity'];

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $materialQuantity)
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

                $material -> save();
            } else {
                $material = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $operation->source_project_object_id,
                    'amount' => $materialAmount,
                    'quantity' => $materialQuantity
                ]);

                if ($materialType->accounting_type == 2) {
                    $material->amount = $materialAmount;
                    $material->quantity = $materialQuantity;
                } else {
                    $material->amount = 1;
                    $material->quantity = $materialQuantity * $materialAmount;
                }

                $material->save();
            }
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

        if (isset($requestData->new_comment)) {
            $materialOperationComment = new q3wOperationComment([
                'material_operation_id' => $operation->id,
                'operation_route_stage_id' => $operation->operation_route_stage_id,
                'comment' => $requestData->new_comment,
                'user_id' => Auth::id()
            ]);

            $materialOperationComment->save();
        }

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

        DB::commit();
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

            $operation->operation_route_stage_id = 72;
            $operation->save();

            $this->sendTransformationNotification($operation,
                'Операция отменена руководителем',
                $operation->source_responsible_user_id,
                $operation->source_project_object_id
            );

            $operation->operation_route_stage_id = 74;
            $operation->save();

            DB::commit();
        }
    }

    public function isUserResponsibleForMaterialAccounting(int $projectObjectId): bool
    {
        return ObjectResponsibleUser::where('user_id', Auth::id())
            ->where('object_id', $projectObjectId)->exists();
    }

    public function allowEditing(q3wMaterialOperation $operation): bool
    {
        return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
    }

    public function allowCancelling(q3wMaterialOperation $operation): bool
    {
        return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
    }
}