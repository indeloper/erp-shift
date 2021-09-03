<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\ProjectObject;
use App\models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\models\q3wMaterial\q3wMaterial;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialComment;
use App\models\q3wMaterial\q3wMaterialSnapshot;
use App\models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\q3wMaterial\q3wOperationMaterialComment;
use App\Models\User;
use Carbon\Carbon;
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

class q3wMaterialSupplyOperationController extends Controller
{
    const EMPTY_COMMENT_TEXT = "Комментарий не указан";

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


        return view('materials.operations.supply.new')->with([
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
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name')->toJson(JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function validateMaterialList(Request $request)
    {
        $errors = [];

        $validationData = json_decode($request->getContent(), false);

        if (empty($validationData->materials)) {
            $errors['common']['errorList'][] = (object)['severity' => 1000, 'type' => 'materialsNotFound', 'message' => 'Материалы не указаны'];
        } else {
            $materials = $validationData->materials;
        }

        $projectObject = ProjectObject::find($validationData->project_object_id);
        if (empty($projectObject)) {
            $errors['common']['errorList'][] = (object)['severity' => 1000, 'type' => 'destinationProjectObjectNotFound', 'message' => 'Объект назначения не найден'];
        }

        if (!empty($validationData->materials)) {
            foreach ($materials as $material) {
                $materialStandard = q3wMaterialStandard::find($material->standard_id);

                if (empty($materialStandard)) {
                    $errors['common']['errorList'][] = (object)['severity' => 1000, 'type' => 'materialStandardNotFound', 'message' => 'Эталона материала с идентификатором "' . $material->standard_id . '" не существует'];
                    continue;
                }

                $key = $material->validationUid;

                $materialName = $materialStandard->name;

                if ($materialStandard->accounting_type == 2 && !empty($material->quantity)) {
                    $materialName .= ' ('.$material->quantity.' м.п)';
                }

                if (empty($material->amount)) {
                    $errors[$key]['errorList'][] = (object)['severity' => 1000, 'type' => 'amountIsNull', 'itemName' => $materialName, 'message' => 'Количество в штуках не указано'];
                }

                if (empty($material->quantity)) {
                    $errors[$key]['errorList'][] = (object)['severity' => 1000, 'type' => 'quantityIsNull', 'itemName' => $materialName, 'message' => 'Количество в единицах измерения не указано'];
                }

                if (!empty($material->quantity)) {
                    if ($materialStandard->materialType->measure_unit == 1) {
                        if ($material->quantity > 15) {
                            $errors[$key]['errorList'][] = (object)['severity' => 500, 'type' => 'largeMaterialLength', 'itemName' => $materialName, 'message' => 'Габарит груза превышает 15 м.п.'];
                        }
                    }
                }

                if (isset($errors[$key]['errorList'])) {
                    $errors[$key]['isValid'] = false;
                } else {
                    $errors[$key]['isValid'] = true;
                }
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
            'operation_route_id' => 1,
            'operation_route_stage_id' => 3,
            'destination_project_object_id' => $requestData['project_object_id'],
            'operation_date' => $requestData['operation_date'],
            'creator_user_id' => Auth::id(),
            'destination_responsible_user_id' => $requestData['destination_responsible_user_id'],
            'contractor_id' => $requestData['contractor_id'],
            'consignment_note_number' => $requestData['consignment_note_number'],
        ]);
        $materialOperation->save();

        if (!empty($requestData['new_comment'])){
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
            $materialType = $materialStandard->materialType;

            $inputMaterialAmount = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];
            if (empty($inputMaterial['comment'])) {
                $inputMaterialComment = null;
            } else {
                $inputMaterialComment = $inputMaterial['comment'];
            }

            if (isset($inputMaterialComment)) {
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
                'comment_id' => $materialCommentId
            ]);

            $operationMaterial->save();

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->leftJoin('q3w_material_comments', 'comment_id', '=', 'q3w_material_comments.id')
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $inputMaterialQuantity)
                    ->where(function ($query) use ($inputMaterialComment) {
                        if (!empty($inputMaterialComment)) {
                            $query->where('comment', '=', $inputMaterialComment);
                        } else {
                            $query->whereNull('comment_id');
                        }
                    })
                    ->get(['q3w_materials.*',
                            'q3w_material_comments.comment'])
                    ->first();
            } else {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $materialStandard->id)
                    ->first();
            }

            if (isset($material)) {
                if ($materialType->accounting_type == 2) {
                    $material->amount = $material->amount + $inputMaterialAmount;
                } else {
                    $material->amount = 1;
                    $material->quantity = $material->quantity + $inputMaterialQuantity * $inputMaterialAmount;
                }

                $material -> save();
            } else {
                if (!empty($inputMaterialComment)) {
                    $materialComment = new q3wMaterialComment([
                        'comment' => $inputMaterialComment,
                        'author_id' => Auth::id()
                    ]);
                    $materialComment->save();
                    $materialCommentId = $materialComment->id;
                } else {
                    $materialCommentId = null;
                }

                $material = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $requestData['project_object_id'],
                    'amount' => $inputMaterialAmount,
                    'quantity' => $inputMaterialQuantity,
                    'comment_id' => $materialCommentId
                ]);

                if ($materialType->accounting_type == 2) {
                    $material->amount = $inputMaterialAmount;
                    $material->quantity = $inputMaterialQuantity;
                } else {
                    $material->amount = 1;
                    $material->quantity = $inputMaterialQuantity * $inputMaterialAmount;
                }

                $material->save();
            }
        }

        foreach ($requestData['uploaded_files'] as $uploadedFileId) {
            $uploadedFile = q3wOperationFile::find($uploadedFileId);
            $uploadedFile->material_operation_id = $materialOperation->id;
            $uploadedFile->operation_route_stage_id = $materialOperation->operation_route_stage_id;
            $uploadedFile->save();
        }

        (new q3wMaterialSnapshot)->takeSnapshot($materialOperation, ProjectObject::find($requestData['project_object_id']));

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
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
}
