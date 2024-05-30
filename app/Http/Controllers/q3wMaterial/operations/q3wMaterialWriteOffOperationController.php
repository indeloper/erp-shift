<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Http\Controllers\Controller;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Permission;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialSnapshot;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\q3wMaterial\q3wOperationMaterialComment;
use App\Models\User;
use App\Models\UserPermission;
use App\Notifications\Task\TaskPostponedAndClosedNotice;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class q3wMaterialWriteOffOperationController extends Controller
{
    const EMPTY_COMMENT_TEXT = 'Комментарий не указан';

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(Request $request): View
    {

        if (isset($request->project_object)) {
            $projectObjectId = $request->project_object;
        } else {
            $projectObjectId = 0;
        }

        if (isset($request->materialsToWriteOff)) {
            $predefinedMaterialsArray = explode('+', $request->materialsToWriteOff);

            $predefinedMaterials = DB::table('q3w_materials as a')
                ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=', 'b.id')
                ->leftJoin('q3w_material_types as d', 'b.material_type', '=', 'd.id')
                ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
                ->leftJoin('q3w_material_comments as f', 'a.comment_id', '=', 'f.id')
                ->where('a.project_object', '=', $projectObjectId)
                ->whereIn('a.id', $predefinedMaterialsArray)
                ->get(['a.id',
                    'a.standard_id',
                    'a.amount',
                    'a.amount as total_amount',
                    'a.quantity',
                    'a.quantity as total_quantity',
                    'a.comment_id',
                    'a.comment_id as initial_comment_id',
                    'f.comment',
                    'f.comment as initial_comment',
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
                            })
                            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                            ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                            ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                            ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                            ->where('q3w_material_operations.source_project_object_id', $projectObjectId)
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
                            ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
                            ->where('q3w_material_operations.source_project_object_id', $projectObjectId)
                            ->get(DB::raw('sum(`quantity`) as quantity'))
                            ->first();
                        $material->total_quantity = round($material->total_quantity - $activeOperationMaterialAmount->quantity, 2);
                }

                $material->validationUid = Str::uuid();
            }
        } else {
            $predefinedMaterials = [];
        }

        //dd($predefinedMaterials);

        return view('materials.operations.write-off.new')->with([
            'projectObjectId' => $projectObjectId,
            'currentUserId' => Auth::id(),
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => DB::table('q3w_material_standards as a')
                ->leftJoin('q3w_material_types as b', 'a.material_type', '=', 'b.id')
                ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
                ->get(['a.*', 'b.name as material_type_name', 'b.measure_unit', 'b.accounting_type', 'd.value as measure_unit_value'])
                ->toJSON(),
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name')->toJson(JSON_UNESCAPED_UNICODE),
            'users' => User::getAllUsers()->where('status', 1)->get()->toJson(JSON_UNESCAPED_UNICODE),
            'predefinedMaterials' => json_encode($predefinedMaterials),
        ]);
    }

    public function validateMaterialList(Request $request): JsonResponse
    {
        $errors = [];

        $validationData = json_decode($request->getContent(), false);

        if (empty($validationData->operationId)) {
            $operationId = 0;
        } else {
            $operationId = $validationData->operationId;
        }

        if (empty($validationData->materials)) {
            $errors['common']['errorList'][] = (object) ['severity' => 1000, 'type' => 'materialsNotFound', 'message' => 'Материалы не указаны'];
        } else {
            $materials = $validationData->materials;
        }

        $projectObject = ProjectObject::find($validationData->projectObjectId);
        if (empty($projectObject)) {
            $errors['common']['errorList'][] = (object) ['severity' => 1000, 'type' => 'sourceProjectObjectNotFound', 'message' => 'Объект отправления не найден'];
        }

        if (! empty($materials)) {
            $unitedMaterials = [];

            foreach ($materials as $material) {
                if (isset($material->edit_states) && in_array('deletedByRecipient', $material->edit_states)) {
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
                            if ($unitedMaterials[$key]->max_quantity < $material->quantity) {
                                $unitedMaterials[$key]->max_quantity = $material->quantity;
                            }
                            $unitedMaterials[$key]->quantity = round($unitedMaterials[$key]->quantity + $material->quantity * $material->amount, 2);
                            $unitedMaterials[$key]->amount = 1;
                        } else {
                            $unitedMaterials[$key] = $material;
                            $unitedMaterials[$key]->max_quantity = $material->quantity;
                            $unitedMaterials[$key]->quantity = round($material->quantity * $material->amount, 2);
                            $unitedMaterials[$key]->amount = 1;
                        }
                }

                if (! isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                    $errors[$key]['errorList'][] = (object) ['severity' => 1000, 'type' => 'amountIsNull', 'itemName' => $materialStandard->name, 'message' => 'Количество в штуках не указано'];
                }

                if (! isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                    $errors[$key]['errorList'][] = (object) ['severity' => 1000, 'type' => 'quantityIsNull', 'itemName' => $materialStandard->name, 'message' => 'Количество в единицах измерения не указано'];
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

                if ($accountingType == 2) {
                    $sourceProjectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObject->id)
                        ->where('standard_id', $unitedMaterial->standard_id)
                        ->where('quantity', $unitedMaterial->quantity)
                        ->where(function ($query) use ($unitedMaterial) {
                            if (empty($unitedMaterial->initial_comment_id)) {
                                $query->whereNull('comment_id');
                            } else {
                                $query->where('comment_id', $unitedMaterial->initial_comment_id);
                            }
                        })
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $unitedMaterial->standard_id)
                        ->where('quantity', $unitedMaterial->quantity)
                        ->where('material_operation_id', '<>', $operationId)
                        ->where('initial_comment_id', $unitedMaterial->initial_comment_id)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
                        ->where('q3w_material_operations.source_project_object_id', $projectObject->id)
                        ->get(DB::raw('sum(`amount`) as amount'))
                        ->first();

                    $operationAmount = $activeOperationMaterialAmount->amount;
                } else {
                    $sourceProjectObjectMaterial = (new q3wMaterial)
                        ->where('project_object', $projectObject->id)
                        ->where('standard_id', $unitedMaterial->standard_id)
                        ->where(function ($query) use ($unitedMaterial) {
                            if (empty($unitedMaterial->initial_comment_id)) {
                                $query->whereNull('comment_id');
                            } else {
                                $query->where('comment_id', $unitedMaterial->initial_comment_id);
                            }
                        })
                        ->get()
                        ->first();

                    $activeOperationMaterialAmount = q3wOperationMaterial::where('standard_id', $unitedMaterial->standard_id)
                        ->where('initial_comment_id', $unitedMaterial->initial_comment_id)
                        ->where('material_operation_id', '<>', $operationId)
                        ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
                        ->leftJoin('q3w_material_operations', 'q3w_material_operations.id', 'material_operation_id')
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
                        ->whereNotIn('q3w_material_operations.operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'))
                        ->whereRaw('IFNULL(`transform_operation_stage_id`, 0) NOT IN (2, 3) ')
                        ->where('q3w_material_operations.source_project_object_id', $projectObject->id)
                        ->get(DB::raw('sum(`amount` * `quantity`) as total_quantity'))
                        ->first();

                    $operationQuantity = $activeOperationMaterialAmount->total_quantity;
                }

                if (! isset($errors[$key])) {
                    if ($materialStandard->materialType->measure_unit == 1) {
                        if ($unitedMaterial->max_quantity > 15) {
                            $errors[$key]['errorList'][] = (object) ['severity' => 500, 'type' => 'largeMaterialLength', 'itemName' => $materialName, 'message' => 'Габарит груза превышает 15 м.п.'];
                        }
                    }
                }

                if (! isset($sourceProjectObjectMaterial)) {
                    $errors[$key]['errorList'][] = (object) ['severity' => 1000, 'type' => 'materialNotFound', 'itemName' => $materialName, 'message' => 'На объекте отправления не существует такого материала'];
                } else {
                    if ($accountingType == 2) {
                        $materialAmountDelta = round($sourceProjectObjectMaterial->amount - $unitedMaterial->amount - $operationAmount, 2);
                    } else {
                        $materialAmountDelta = round($sourceProjectObjectMaterial->quantity - $unitedMaterial->quantity - $operationQuantity, 2);
                    }

                    if (round($materialAmountDelta, 2) < 0) {
                        $errors[$key]['errorList'][] = (object) ['severity' => 1000, 'type' => 'negativeMaterialQuantity', 'itemName' => $materialName, 'message' => 'На объекте отправления недостаточно материала'];
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
                $errors['common']['errorList'][] = (object) ['severity' => 500, 'type' => 'totalWeightTooLarge', 'message' => 'Общая масса материалов превышает 20 т.'];
            }
        }

        $errorResult = [];

        foreach ($errors as $key => $error) {
            if ($key != 'common') {
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
            'timestamp' => $validationData->timestamp,
        ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        $requestData = json_decode($request['data'], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

        //Нужно проверить, что материал существует
        //Нужно проверить, что остаток будет большим, или равным нулю
        foreach ($requestData['materials'] as $inputMaterial) {
            if ($inputMaterial['accounting_type'] == 2) {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->where('quantity', $inputMaterial['quantity'])
                    ->where(function ($query) use ($inputMaterial) {
                        if (empty($inputMaterial['initial_comment_id'])) {
                            $query->whereNull('comment_id');
                        } else {
                            $query->where('comment_id', $inputMaterial['initial_comment_id']);
                        }
                    })
                    ->firstOrFail();

                if ($inputMaterial['amount'] > $sourceMaterial['amount']) {
                    abort(400, 'Bad amount for standard '.$inputMaterial['standard_id']);
                }
            } else {
                $sourceMaterial = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $inputMaterial['standard_id'])
                    ->where(function ($query) use ($inputMaterial) {
                        if (empty($inputMaterial['initial_comment_id'])) {
                            $query->whereNull('comment_id');
                        } else {
                            $query->where('comment_id', $inputMaterial['initial_comment_id']);
                        }
                    })
                    ->firstOrFail();

                if (round($inputMaterial['amount'] * $inputMaterial['quantity'], 2) > round($sourceMaterial['quantity'], 2)) {
                    abort(400, 'Bad quantity for standard '.$inputMaterial['standard_id']);
                }
            }
        }

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id' => 4,
            'operation_route_stage_id' => 75,
            'source_project_object_id' => $requestData['project_object_id'],
            'operation_date' => isset($requestData['operation_date']) ? $requestData['operation_date'] : null,
            'creator_user_id' => Auth::id(),
            'source_responsible_user_id' => $requestData['responsible_user_id'],
        ]);

        $materialOperation->save();

        $newComment = $requestData['new_comment'] ?? self::EMPTY_COMMENT_TEXT;

        $materialOperationComment = new q3wOperationComment([
            'material_operation_id' => $materialOperation->id,
            'operation_route_stage_id' => $materialOperation->operation_route_stage_id,
            'comment' => $newComment,
            'user_id' => Auth::id(),
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

            if (! empty($inputMaterialComment)) {
                $materialComment = new q3wOperationMaterialComment([
                    'comment' => $inputMaterialComment,
                    'author_id' => Auth::id(),
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
                'initial_comment_id' => $inputMaterialInitialCommentId,
                'comment_id' => $materialCommentId,
            ]);

            $operationMaterial->save();
        }

        foreach ($requestData['uploaded_files'] as $uploadedFileId) {
            $uploadedFile = q3wOperationFile::find($uploadedFileId);
            $uploadedFile->material_operation_id = $materialOperation->id;
            $uploadedFile->operation_route_stage_id = $materialOperation->operation_route_stage_id;
            $uploadedFile->save();
        }

        $this->moveOperationToNextStage($materialOperation->id);

        DB::commit();

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    public function view(Request $request): View
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
            'measureUnits' => q3wMeasureUnit::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id', 'value')->toJson(JSON_UNESCAPED_UNICODE),
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
                    'b.weight as standard_weight',
                    'd.measure_unit',
                    'd.name as material_type_name',
                    'd.accounting_type',
                    'e.value as measure_unit_value',
                    'g.comment',
                    'f.comment as initial_comment'])
                ->toJson(JSON_UNESCAPED_UNICODE),
            'allowEditing' => $this->allowEditing($operation),
            'allowCancelling' => $this->allowCancelling($operation),
            'routeStageId' => $operation->operation_route_stage_id,
        ]);
    }

    public function moveOperationToNextStage($operationId, $cancelled = false)
    {
        $operation = q3wMaterialOperation::findOrFail($operationId);
        switch ($operation->operation_route_stage_id) {
            case 75:
                $operation->operation_route_stage_id = 76;
                $operation->save();
                $this->moveOperationToNextStage($operation->id);
                break;
            case 76:
                $operation->operation_route_stage_id = 77;
                $operation->save();
                $this->sendWriteOffNotificationToResponsibilityUsersOfObject($operation,
                    'Необходимо подтвердить списание',
                    $operation->source_project_object_id);
                break;
            case 77:
                if ($cancelled) {
                    $operation->operation_route_stage_id = 80;
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $cancelled);
                } else {
                    $operation->operation_route_stage_id = 78;
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id);
                }
                break;
            case 78:
                $operation->operation_route_stage_id = 79;
                $operation->save();
                $this->sendWriteOffNotificationToResponsibilityUsers($operation,
                    'Необходимо подтвердить списание',
                    $operation->source_project_object_id);
                break;
            case 79:
                if ($cancelled) {
                    $operation->operation_route_stage_id = 80;
                    $operation->save();
                    $this->moveOperationToNextStage($operation->id, $cancelled);
                } else {
                    $this->move($operation);
                    $operation->operation_route_stage_id = 80;
                    $operation->save();
                    $projectObject = ProjectObject::findOrFail($operation->source_project_object_id);
                    (new q3wMaterialSnapshot)->takeSnapshot($operation, $projectObject);
                    $this->moveOperationToNextStage($operation->id);
                }
                break;
            case 80:
                if ($cancelled) {
                    $operation->operation_route_stage_id = 82;
                    $operation->save();
                    $this->sendWriteOffNotification($operation,
                        'Операция отменена',
                        $operation->source_responsible_user_id,
                        $operation->source_project_object_id);
                } else {
                    $operation->operation_route_stage_id = 81;
                    $operation->save();
                    $this->sendWriteOffNotification($operation,
                        'Операция подтверждена, материалы списаны',
                        $operation->source_responsible_user_id,
                        $operation->source_project_object_id);
                }
                break;
        }
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
                    ->where(function ($query) use ($materialToWriteOff) {
                        if (empty($materialToWriteOff['initial_comment_id'])) {
                            $query->whereNull('comment_id');
                        } else {
                            $query->where('comment_id', $materialToWriteOff['initial_comment_id']);
                        }
                    })
                    ->where('quantity', $materialQuantity)
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $operation->source_project_object_id)
                    ->where('standard_id', $materialStandard->id)
                    ->where(function ($query) use ($materialToWriteOff) {
                        if (empty($materialToWriteOff['initial_comment_id'])) {
                            $query->whereNull('comment_id');
                        } else {
                            $query->where('comment_id', $materialToWriteOff['initial_comment_id']);
                        }
                    })
                    ->first();
            }

            if (! isset($material)) {
                abort(400, 'Source material not found');
            }

            if ($materialType->accounting_type == 2) {
                $material->amount = round($material->amount - $materialAmount, 2);
            } else {
                $material->amount = 1;
                $material->quantity = round($material->quantity - $materialQuantity * $materialAmount, 2);
            }

            if ($material->amount < 0) {
                abort(400, 'Negative material amount after write-off');
            }

            if ($material->quantity < 0) {
                abort(400, 'Negative material quantity after write-off');
            }

            $material->save();
        }

    }

    public function confirmOperation(Request $request)
    {
        $requestData = json_decode($request['data']);

        $operation = q3wMaterialOperation::findOrFail($requestData->operationId);
        if (! $this->allowEditing($operation)) {
            abort(400, 'You are in read-only mode');
        }

        DB::beginTransaction();

        $newComment = $requestData->new_comment ?? self::EMPTY_COMMENT_TEXT;

        $materialOperationComment = new q3wOperationComment([
            'material_operation_id' => $operation->id,
            'operation_route_stage_id' => $operation->operation_route_stage_id,
            'comment' => $newComment,
            'user_id' => Auth::id(),
        ]);

        $materialOperationComment->save();

        foreach ($requestData->uploaded_files as $uploadedFileId) {
            $uploadedFile = q3wOperationFile::find($uploadedFileId);
            $uploadedFile->material_operation_id = $operation->id;
            $uploadedFile->operation_route_stage_id = $operation->operation_route_stage_id;
            $uploadedFile->save();
        }

        $this->moveOperationToNextStage($operation->id);

        $operation = q3wMaterialOperation::find($operation->id);
        $materialOperationComment->operation_route_stage_id = $operation->operation_route_stage_id;
        $materialOperationComment->save();

        DB::commit();
    }

    public function cancelOperation(Request $request)
    {
        $requestData = json_decode($request['data']);

        $operation = q3wMaterialOperation::findOrFail($requestData->operationId);
        if ($this->allowCancelling($operation)) {
            DB::beginTransaction();

            $newComment = $requestData->new_comment ?? self::EMPTY_COMMENT_TEXT;

            $materialOperationComment = new q3wOperationComment([
                'material_operation_id' => $operation->id,
                'operation_route_stage_id' => $operation->operation_route_stage_id,
                'comment' => $newComment,
                'user_id' => Auth::id(),
            ]);

            $materialOperationComment->save();

            foreach ($requestData->uploaded_files as $uploadedFileId) {
                $uploadedFile = q3wOperationFile::find($uploadedFileId);
                $uploadedFile->material_operation_id = $operation->id;
                $uploadedFile->operation_route_stage_id = $operation->operation_route_stage_id;
                $uploadedFile->save();
            }

            $this->moveOperationToNextStage($operation->id, true);

            $operation = q3wMaterialOperation::find($operation->id);
            $materialOperationComment->operation_route_stage_id = $operation->operation_route_stage_id;
            $materialOperationComment->save();

            DB::commit();
        }
    }

    public function sendWriteOffNotificationToResponsibilityUsersOfObject(q3wMaterialOperation $operation, string $notificationText, int $projectObjectId)
    {

        $responsibilityUsers = (new ObjectResponsibleUser)->getResponsibilityUsers($projectObjectId, $role = 'TONGUE_PROJECT_MANAGER');

        foreach ($responsibilityUsers as $responsibilityUser) {
            $this->sendWriteOffNotification($operation, $notificationText, $responsibilityUser->user_id, $projectObjectId);
        }
    }

    public function sendWriteOffNotificationToResponsibilityUsers(q3wMaterialOperation $operation, string $notificationText, int $projectObjectId)
    {
        $permissionId = Permission::where('codename', 'material_accounting_write_off_confirmation')->first()->id;
        $responsibilityUsers = UserPermission::where('permission_id', $permissionId)->get();

        foreach ($responsibilityUsers as $responsibilityUser) {
            $this->sendWriteOffNotification($operation, $notificationText, $responsibilityUser->user_id, $projectObjectId);
        }
    }

    public function sendWriteOffNotification(q3wMaterialOperation $operation, string $notificationText, int $notifiedUserId, int $projectObjectId)
    {
        $projectObject = ProjectObject::where('id', $projectObjectId)->first();

        TaskPostponedAndClosedNotice::send(
            $notifiedUserId,
            [
                'name' => 'Операция #'.$operation->id.' от '.$operation->created_at->format('d.m.Y').PHP_EOL.PHP_EOL.
                        $projectObject->short_name.PHP_EOL.PHP_EOL.$notificationText,
                'additional_info' => 'Ссылка на операцию:',
                'url' => $operation->url,
                'target_id' => $operation->id,
                'object_id' => $projectObjectId,
                'created_at' => now(),
                'status' => 7,
            ]
        );
    }

    public function isUserResponsibleForMaterialWriteOff(): bool
    {
        $permissionId = Permission::where('codename', 'material_accounting_write_off_confirmation')->first()->id;

        return UserPermission::where('permission_id', $permissionId)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function isUserResponsibleForMaterialAccounting(int $projectObjectId): bool
    {
        return (new ObjectResponsibleUser)->isUserResponsibleForObject(Auth::id(), $projectObjectId, $role = 'TONGUE_PROJECT_MANAGER');
    }

    public function allowEditing(q3wMaterialOperation $operation): bool
    {
        switch ($operation->operation_route_stage_id) {
            case 77:
                return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 79:
                return $this->isUserResponsibleForMaterialWriteOff();
            default:
                return false;
        }
    }

    public function allowCancelling(q3wMaterialOperation $operation): bool
    {
        switch ($operation->operation_route_stage_id) {
            case 77:
                return $this->isUserResponsibleForMaterialAccounting($operation->source_project_object_id);
            case 79:
                return $this->isUserResponsibleForMaterialWriteOff();
            default:
                return false;
        }
    }

    public function completed(Request $request): View
    {
        $operation = q3wMaterialOperation::leftJoin('project_objects as source_project_objects', 'source_project_objects.id', '=', 'q3w_material_operations.source_project_object_id')
            ->leftJoin('users as source_users', 'source_users.id', '=', 'q3w_material_operations.source_responsible_user_id')
            ->get(['q3w_material_operations.*',
                'source_project_objects.short_name as source_project_object_name',
                DB::Raw('CONCAT(`source_users`.`last_name`, " ", UPPER(SUBSTRING(`source_users`.`first_name`, 1, 1)), ". ", UPPER(SUBSTRING(`source_users`.`patronymic`, 1, 1)), ".") as source_responsible_user_name'),
            ])
            ->where('id', '=', $request->operationId)
            ->first();

        if (! isset($operation)) {
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
            ->whereRaw("NOT IFNULL(JSON_CONTAINS(`edit_states`, json_array('deletedByRecipient')), 0)") //TODO - переписать в нормальный реляционный вид вместо JSON
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
                'j.comment',
            ])
            ->toJson(JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        return view('materials.operations.write-off.completed')->with([
            'operationData' => $operationData,
            'operationMaterials' => $materials,
            'operationRouteStage' => $operationRouteStage,
            'materialTypes' => q3wMaterialType::all('id', 'name')->toJson(JSON_UNESCAPED_UNICODE),
        ]);
    }
}
