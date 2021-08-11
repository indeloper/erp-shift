<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\Notification;
use App\Models\Permission;
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
use App\Models\UserPermission;
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

class q3wMaterialWriteOffOperationController extends Controller
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
        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = 0;
        }


        return view('materials.operations.write-off.new')->with([
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

    public function validateMaterialList(Request $request)
    {
        $errors = [];

        if (isset($request->materials)) {
            $materials = $request->materials;
        } else {
            $errors['common'][] = (object)['severity' => 1000, 'type' => 'materialsNotFound', 'message' => 'Материалы не указаны'];
        }

        $projectObject = ProjectObject::find($request->project_object_id);
        if (!isset($projectObject)) {
            $errors['common'][] = (object)['severity' => 1000, 'type' => 'sourceProjectObjectNotFound', 'message' => 'Объект не найден'];
        }



        if (isset($request->materials)) {
            $unitedMaterials = [];

            foreach ($materials as $material) {
                $material = (object)$material;

                $materialStandard = q3wMaterialStandard::find($material->standard_id);

                $accountingType = $materialStandard->materialType->accounting_type;

                switch ($accountingType) {
                    case 2:
                        $key = $material->standard_id . '-' . $material->quantity;
                        if (array_key_exists($key, $unitedMaterials)) {
                            $unitedMaterials[$key]->amount = $unitedMaterials[$key]->amount + $material->amount;
                        } else {
                            $unitedMaterials[$key] = $material;
                        }
                        break;
                    default:
                        $key = $material->standard_id;
                        if (array_key_exists($key, $unitedMaterials)) {
                            $unitedMaterials[$key]->quantity = $unitedMaterials[$key]->quantity + $material->quantity * $material->amount;
                            $unitedMaterials[$key]->amount = $unitedMaterials[$key]->amount + $material->amount;
                        } else {
                            $unitedMaterials[$key] = $material;
                            $unitedMaterials[$key]->quantity = $material->quantity * $material->amount;
                        }
                }

                if (!isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'amountIsNull', 'itemName' => $materialStandard->name, 'message' => 'Количество в штуках не указано'];
                }

                if (!isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'quantityIsNull', 'itemName' => $materialStandard->name, 'message' => 'Количество в единицах измерения не указано'];
                }
            }

            foreach ($unitedMaterials as $key => $unitedMaterial) {
                $materialStandard = q3wMaterialStandard::find($unitedMaterial->standard_id);

                $accountingType = $materialStandard->materialType->accounting_type;

                $materialName = $materialStandard->name;

                if (isset($errors[$key]) && count($errors[$key]) > 0) {
                    continue;
                }

                if ($accountingType == 2) {
                    $sourceProjectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObject->id)
                        ->where('standard_id', $unitedMaterial->standard_id)
                        ->where('quantity', $unitedMaterial->quantity)
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $unitedMaterial->standard_id)
                        ->where('quantity', $unitedMaterial->quantity)
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->where('q3w_material_operations.source_project_object_id', $projectObject->id)
                        ->get(DB::raw('sum(`amount`) as amount'))
                        ->first();

                    $operationAmount = $activeOperationMaterialAmount->amount;
                } else {
                    $sourceProjectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObject->id)
                        ->where('standard_id', $unitedMaterial->standard_id)
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $unitedMaterial->standard_id)
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->where('q3w_material_operations.source_project_object_id', $projectObject->id)
                        ->get(DB::raw('sum(`amount`) as amount, sum(`quantity`) as quantity'))
                        ->first();

                    $operationAmount = $activeOperationMaterialAmount->amount;
                    $operationQuantity = $activeOperationMaterialAmount->quantity;
                }

                if (!isset($errors[$key])) {
                    if ($materialStandard->materialType->measure_unit == 1) {
                        if ($unitedMaterial->quantity > 15) {
                            $errors[$key][] = (object)['severity' => 500, 'type' => 'largeMaterialLength', 'itemName' => $materialName, 'message' => 'Габарит груза превышает 15 м.п.'];
                        }
                    }
                }

                if (!isset($sourceProjectObjectMaterial)) {
                    $errors[$key][] = (object)['severity' => 1000, 'type' => 'materialNotFound', 'itemName' => $materialName, 'message' => 'На объекте отправления не существует такого материала'];
                } else {
                    if ($accountingType == 2) {
                        $materialAmountDelta = $sourceProjectObjectMaterial->amount - $unitedMaterial->amount - $operationAmount;
                    } else {
                        $materialAmountDelta = $sourceProjectObjectMaterial->quantity - $unitedMaterial->quantity - $operationQuantity * $operationAmount;
                        //dd($unitedMaterial);
                    }

                    if ($materialAmountDelta < 0) {
                        $errors[$key][] = (object)['severity' => 1000, 'type' => 'negativeMaterialQuantity', 'itemName' => $materialName, 'message' => 'На объекте отправления недостаточно материала'];
                    }
                }
            }

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

        //Нужно проверить, что материал существует
        //Нужно проверить, что остаток будет большим, или равным нулю
        foreach ($requestData['materials'] as $inputMaterial) {
            if ($inputMaterial['accounting_type'] == 2) {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->where('quantity', $inputMaterial['quantity'])
                    ->firstOrFail();

                if ($inputMaterial['amount'] > $sourceMaterial['amount']) {
                    abort(400, 'Bad quantity for standard ' . $inputMaterial['standard_id']);
                }
            } else {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->firstOrFail();

                if ($inputMaterial['amount'] > $sourceMaterial['quantity']) {
                    abort(400, 'Bad quantity for standard ' . $inputMaterial['standard_id']);
                }
            }
        }

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 4,
            'operation_route_stage_id' => 75,
            'source_project_object_id' => $requestData['project_object_id'],
            'operation_date' => isset($requestData['operation_date']) ? $requestData['operation_date'] : null,
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

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);

            $inputMaterialAmount = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $inputMaterialAmount,
                'quantity' => $inputMaterialQuantity,
                'initial_amount' => $inputMaterialAmount,
                'initial_quantity' => $inputMaterialQuantity
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

        DB::beginTransaction();

        $materialOperation->operation_route_stage_id = 76;
        $materialOperation->save();

        $this->sendWriteOffNotificationToResponsibilityUsers($materialOperation,
            'Необходимо подтвердить списание',
            $materialOperation->source_project_object_id);

        $materialOperation->operation_route_stage_id = 77;
        $materialOperation->save();

        DB::commit();

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    public function view(Request $request)
    {
        if (isset($request->operationId)) {
            $operation = q3wMaterialOperation::findOrFail($request->operationId);
        }

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);
        $operationRouteStage = q3wOperationRouteStage::find($operation->operation_route_stage_id)->name;

        return view('materials.operations.write-off.view')->with([
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

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        $requestData = json_decode($request["data"]);

        dd($requestData->materials);
    }

    public function move(q3wMaterialOperation $operation)
    {
        $materialsToWriteOff = q3wOperationMaterial::where('material_operation_id', '=', $operation->id)
            ->get()
            ->toArray();

        foreach ($materialsToWriteOff as $materialToWriteOff) {
            $materialStandard = q3wMaterialStandard::findOrFail($materialToWriteOff['standard_id']);
            $materialType = $materialStandard->materialType;

            $materialAmount = $materialToWriteOff['amount'];
            $materialQuantity = $materialToWriteOff['quantity'];

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

        $operation->operation_route_stage_id = 78;
        $operation->save();

        $this->sendWriteOffNotification($operation,
            'Операция подтверждена руководителем',
            $operation->source_responsible_user_id,
            $operation->source_project_object_id
        );

        $operation->operation_route_stage_id = 79;
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

            $operation->operation_route_stage_id = 80;
            $operation->save();

            $this->sendWriteOffNotification($operation,
                'Операция отменена',
                $operation->source_responsible_user_id,
                $operation->source_project_object_id
            );

            $operation->operation_route_stage_id = 74;
            $operation->save();

            DB::commit();
        }
    }

    public function sendWriteOffNotificationToResponsibilityUsers(q3wMaterialOperation $operation, string $notificationText, int $projectObjectId) {
        $permissionId = Permission::where('codename', 'material_accounting_write_off_confirmation')->first()->id;
        $responsibilityUsers = UserPermission::where('permission_id', $permissionId)->get();

        foreach ($responsibilityUsers as $responsibilityUser) {
            $this->sendWriteOffNotification($operation, $notificationText, $responsibilityUser->user_id, $projectObjectId);
        }
    }

    public function sendWriteOffNotification(q3wMaterialOperation $operation, string $notificationText, int $notifiedUserId, int $projectObjectId){
        $projectObject = ProjectObject::where('id', $projectObjectId)->first();

        $notificationText = 'Операция #' . $operation->id . ' от ' . $operation->created_at->format('d.m.Y') . PHP_EOL . PHP_EOL . $projectObject->short_name . PHP_EOL . PHP_EOL . $notificationText;

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = PHP_EOL . $operation->url . $operation->id;
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

    public function isUserResponsibleForMaterialWriteOff(): bool
    {
        $permissionId = Permission::where('codename', 'material_accounting_write_off_confirmation')->first()->id;
        return UserPermission::where('permission_id', $permissionId)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function allowEditing(q3wMaterialOperation $operation): bool
    {
        return $this->isUserResponsibleForMaterialWriteOff();
    }

    public function allowCancelling(q3wMaterialOperation $operation): bool
    {
        return $this->isUserResponsibleForMaterialWriteOff();
    }
}
