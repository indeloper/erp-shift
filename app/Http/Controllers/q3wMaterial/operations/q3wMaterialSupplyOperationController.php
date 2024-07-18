<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Http\Controllers\Controller;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
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
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class q3wMaterialSupplyOperationController extends Controller
{

    const EMPTY_COMMENT_TEXT = 'Комментарий не указан';

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

        return view('materials.operations.supply.new')->with([
            'projectObjectId'   => $projectObjectId,
            'currentUserId'     => Auth::id(),
            'measureUnits'      => q3wMeasureUnit::all('id', 'value')
                ->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes'   => q3wMaterialAccountingType::all('id', 'value')
                ->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes'     => q3wMaterialType::all('id', 'name')
                ->toJson(JSON_UNESCAPED_UNICODE),
            'materialStandards' => DB::table('q3w_material_standards as a')
                ->leftJoin('q3w_material_types as b', 'a.material_type', '=',
                    'b.id')
                ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=',
                    'd.id')
                ->get([
                    'a.*', 'b.name as material_type_name', 'b.measure_unit',
                    'b.accounting_type', 'd.value as measure_unit_value',
                ])
                ->toJSON(),
            'projectObjects'    => ProjectObject::all('id', 'name',
                'short_name')->toJson(JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function validateMaterialList(Request $request): JsonResponse
    {
        $errors = [];

        $validationData = json_decode($request->getContent(), false);

        if (empty($validationData->materials)) {
            $errors['common']['errorList'][] = (object) [
                'severity' => 1000, 'type' => 'materialsNotFound',
                'message'  => 'Материалы не указаны',
            ];
        } else {
            $materials = $validationData->materials;
        }

        $projectObject
            = ProjectObject::find($validationData->project_object_id);
        if (empty($projectObject)) {
            $errors['common']['errorList'][] = (object) [
                'severity' => 1000,
                'type'     => 'destinationProjectObjectNotFound',
                'message'  => 'Объект назначения не найден',
            ];
        }

        $totalWeight = 0;

        if ( ! empty($validationData->materials)) {
            foreach ($materials as $material) {
                $materialStandard
                    = q3wMaterialStandard::find($material->standard_id);

                if (empty($materialStandard)) {
                    $errors['common']['errorList'][]
                        = (object) [
                        'severity' => 1000,
                        'type'     => 'materialStandardNotFound',
                        'message'  => 'Эталона материала с идентификатором "'
                            .$material->standard_id.'" не существует',
                    ];

                    continue;
                }

                $key = $material->validationUid;

                $materialName = $materialStandard->name;

                if ($materialStandard->accounting_type == 2
                    && ! empty($material->quantity)
                ) {
                    $materialName .= ' ('.$material->quantity.' м.п)';
                }

                if (empty($material->amount)) {
                    $errors[$key]['errorList'][] = (object) [
                        'severity' => 1000, 'type' => 'amountIsNull',
                        'itemName' => $materialName,
                        'message'  => 'Количество в штуках не указано',
                    ];
                }

                if (empty($material->quantity)) {
                    $errors[$key]['errorList'][] = (object) [
                        'severity' => 1000, 'type' => 'quantityIsNull',
                        'itemName' => $materialName,
                        'message'  => 'Количество в единицах измерения не указано',
                    ];
                }

                if ( ! empty($material->quantity)) {
                    if ($materialStandard->materialType->measure_unit == 1) {
                        if ($material->quantity > 15) {
                            $errors[$key]['errorList'][]
                                = (object) [
                                'severity' => 500,
                                'type'     => 'largeMaterialLength',
                                'itemName' => $materialName,
                                'message'  => 'Габарит груза превышает 15 м.п.',
                            ];
                        }
                    }
                }

                if ( ! empty($material->amount)
                    && ! empty($material->quantity)
                ) {
                    $totalWeight = $material->amount * $material->quantity
                        * $materialStandard->weight;
                }

                if (isset($errors[$key]['errorList'])) {
                    $errors[$key]['isValid'] = false;
                } else {
                    $errors[$key]['isValid'] = true;
                }
            }
        }

        if ($totalWeight > 20) {
            $errors['common']['errorList'][] = (object) [
                'severity' => 500, 'type' => 'totalWeightIsTooLarge',
                'message'  => 'Общий вес груза превышает 20 т',
            ];
        }

        $errorResult = [];

        foreach ($errors as $key => $error) {
            if ($key != 'common') {
                if ($error['isValid']) {
                    $errorResult[] = [
                        'validationUid' => $key, 'isValid' => $error['isValid'],
                    ];
                } else {
                    $errorResult[] = [
                        'validationUid' => $key, 'isValid' => $error['isValid'],
                        'errorList'     => $error['errorList'],
                    ];
                }
            } else {
                $errorResult[] = [
                    'validationUid' => $key, 'isValid' => false,
                    'errorList'     => $error['errorList'],
                ];
            }
        }

        return response()->json([
            'validationResult' => $errorResult,
            'timestamp'        => $validationData->timestamp,
        ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        $requestData = json_decode($request['data'],
            JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/);

        $materialOperation = new q3wMaterialOperation([
            'operation_route_id'              => 1,
            'operation_route_stage_id'        => 3,
            'destination_project_object_id'   => $requestData['project_object_id'],
            'operation_date'                  => $requestData['operation_date'],
            'creator_user_id'                 => Auth::id(),
            'destination_responsible_user_id' => $requestData['destination_responsible_user_id'],
            'contractor_id'                   => $requestData['contractor_id'],
            'consignment_note_number'         => $requestData['consignment_note_number'],
            'material_operation_reason_id'    => $requestData['material_operation_reason_id'],
        ]);
        $materialOperation->save();

        if ( ! empty($requestData['new_comment'])) {
            $newComment = $requestData['new_comment'];
        } else {
            $newComment = self::EMPTY_COMMENT_TEXT;
        }

        $materialOperationComment = new q3wOperationComment([
            'material_operation_id'    => $materialOperation->id,
            'operation_route_stage_id' => $materialOperation->operation_route_stage_id,
            'comment'                  => $newComment,
            'user_id'                  => Auth::id(),
        ]);

        $materialOperationComment->save();

        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard
                          = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);
            $materialType = $materialStandard->materialType;

            $inputMaterialAmount   = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];

            // Если коммит пустой, или в нем нет ни одной буквы
            if ( ! isset($inputMaterial['comment'])
                || empty(trim($inputMaterial['comment']))
                || ! preg_match('/[\p{L}\p{N}]/u', $inputMaterial['comment'])
            ) {
                $inputMaterialComment = null;
            } else {
                $inputMaterialComment = $inputMaterial['comment'];
            }

            if (isset($inputMaterialComment)) {
                $materialComment = new q3wOperationMaterialComment([
                    'comment'   => $inputMaterialComment,
                    'author_id' => Auth::id(),
                ]);
                $materialComment->save();
                $materialCommentId = $materialComment->id;
            } else {
                $materialCommentId = null;
            }

            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id'           => $materialStandard->id,
                'amount'                => $inputMaterialAmount,
                'quantity'              => $inputMaterialQuantity,
                'comment_id'            => $materialCommentId,
            ]);

            $operationMaterial->save();

            $material = q3wMaterial::where('project_object',
                $requestData['project_object_id'])
                ->leftJoin('q3w_material_comments', 'comment_id', '=',
                    'q3w_material_comments.id')
                ->where('standard_id', $materialStandard->id)
                ->where(function ($query) use (
                    $materialType,
                    $inputMaterialQuantity
                ) {
                    switch ($materialType->accounting_type) {
                        case 2:
                            $query->where('quantity', '=',
                                $inputMaterialQuantity);
                            break;
                    }
                })
                ->where(function ($query) use ($inputMaterialComment) {
                    if ( ! empty($inputMaterialComment)) {
                        $query->where('comment', '=', $inputMaterialComment);
                    } else {
                        $query->whereNull('comment_id');
                    }
                })
                ->get([
                    'q3w_materials.*',
                    'q3w_material_comments.comment',
                ])
                ->first();

            if (isset($material)) {
                if ($materialType->accounting_type == 2) {
                    $material->amount = $material->amount
                        + $inputMaterialAmount;
                } else {
                    $material->amount   = 1;
                    $material->quantity = $material->quantity
                        + $inputMaterialQuantity * $inputMaterialAmount;
                }

                $material->save();
            } else {
                if ( ! empty($inputMaterialComment)) {
                    $materialComment = new q3wMaterialComment([
                        'comment'   => $inputMaterialComment,
                        'author_id' => Auth::id(),
                    ]);
                    $materialComment->save();
                    $materialCommentId = $materialComment->id;
                } else {
                    $materialCommentId = null;
                }

                $material = new q3wMaterial([
                    'standard_id'    => $materialStandard->id,
                    'project_object' => $requestData['project_object_id'],
                    'amount'         => $inputMaterialAmount,
                    'quantity'       => $inputMaterialQuantity,
                    'comment_id'     => $materialCommentId,
                ]);

                if ($materialType->accounting_type == 2) {
                    $material->amount   = $inputMaterialAmount;
                    $material->quantity = $inputMaterialQuantity;
                } else {
                    $material->amount   = 1;
                    $material->quantity = $inputMaterialQuantity
                        * $inputMaterialAmount;
                }

                $material->save();
            }
        }

        foreach ($requestData['uploaded_files'] as $uploadedFileId) {
            $uploadedFile                        = q3wOperationFile::find($uploadedFileId);
            $uploadedFile->material_operation_id = $materialOperation->id;
            $uploadedFile->operation_route_stage_id
                                                 = $materialOperation->operation_route_stage_id;
            $uploadedFile->save();
        }

        (new q3wMaterialSnapshot())->takeSnapshot($materialOperation,
            ProjectObject::find($requestData['project_object_id']));

        DB::commit();

        return response()->json([
            'result' => 'ok',
        ], 200);
    }

    public function completed(Request $request): View
    {
        $operation = q3wMaterialOperation::leftJoin('project_objects',
            'project_objects.id', '=',
            'q3w_material_operations.destination_project_object_id')
            ->leftJoin('users', 'users.id', '=',
                'q3w_material_operations.destination_responsible_user_id')
            ->leftJoin('contractors', 'contractors.id', '=',
                'q3w_material_operations.contractor_id')
            ->leftJoin('q3w_material_operation_reasons',
                'q3w_material_operation_reasons.id', '=',
                'q3w_material_operations.material_operation_reason_id')
            ->get([
                'q3w_material_operations.*',
                'project_objects.short_name as destination_project_object_name',
                'contractors.short_name as contractor_name',
                'q3w_material_operation_reasons.name as material_operation_reason_name',
                DB::Raw('CONCAT(`users`.`last_name`, " ", UPPER(SUBSTRING(`users`.`first_name`, 1, 1)), ". ", UPPER(SUBSTRING(`users`.`patronymic`, 1, 1)), ".") as destination_responsible_user_name'),
            ])
            ->where('id', '=', $request->operationId)
            ->first();

        if ( ! isset($operation)) {
            abort(404, 'Операция не найдена');
        }

        $operationData = $operation->toJSON(JSON_OBJECT_AS_ARRAY);
        $operationRouteStage
                       = q3wOperationRouteStage::find($operation->operation_route_stage_id)->name;

        $materials = DB::table('q3w_operation_materials as a')
            ->leftJoin('q3w_material_standards as b', 'a.standard_id', '=',
                'b.id')
            ->leftJoin('q3w_material_types as d', 'b.material_type', '=',
                'd.id')
            ->leftJoin('q3w_measure_units as e', 'd.measure_unit', '=', 'e.id')
            ->leftJoin('q3w_material_operations as f',
                'a.material_operation_id', '=', 'f.id')
            ->leftJoin('q3w_materials as g', 'a.standard_id', '=',
                'g.standard_id')
            ->leftJoin('q3w_operation_material_comments as j', 'a.comment_id',
                '=', 'j.id')
            ->where('a.material_operation_id', '=', $operation->id)
            ->distinct()
            ->get([
                'a.id',
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

        return view('materials.operations.supply.completed')->with([
            'operationData'       => $operationData,
            'operationMaterials'  => $materials,
            'operationRouteStage' => $operationRouteStage,
            'materialTypes'       => q3wMaterialType::all('id', 'name')
                ->toJson(JSON_UNESCAPED_UNICODE),
        ]);
    }

}
