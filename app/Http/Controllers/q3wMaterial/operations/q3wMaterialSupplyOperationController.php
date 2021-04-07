<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\ProjectObject;
use App\models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
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

class q3wMaterialSupplyOperationController extends Controller
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
            'projectObjects' => ProjectObject::all('id', 'name', 'short_name')->toJson(JSON_UNESCAPED_UNICODE),
            'users' => User::getAllUsers()->where('status', 1)->get()->toJson(JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function validateMaterialTransfer(array $materials)
    {
        foreach ($materials as $material) {
            $errors = [];
            $materialStandard = q3wMaterialStandard::find($material->standard_id);
            if (!isset($materialStandard)) {
                //errors[] = array ('')
                continue;
            }

            //$sourceMaterial = q3w
        }
    }

    public function validateMaterialList(Request $request)
    {
        $errors = [];

        $requestData = json_decode($request['data']);

        $projectObject = ProjectObject::find($requestData->project_object_id);

        if (!isset($projectObject)) {
            $errors['destinationProjectObjectNotFound'] = 'Объект получения не найден';
        }

        foreach ($requestData->materials as $material) {
            if (!isset($material->amount) || $material->amount == null || $material->amount == 0 || $material->amount == '') {
                $errors['amountIsNull'][] = array('id' => $material->id, 'message' => 'Количество в штуках не указано');
            }

            if (!isset($material->quantity) || $material->quantity == null || $material->quantity == 0 || $material->quantity == '') {
                $errors['quantityIsNull'][] = array('id' => $material->id, 'message' => 'Количество не указано');
            }
        }

        if (count($errors) == 0) {
            return response()->json([
                'result' => 'ok'
            ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'result' => 'error',
                'errors' => $errors
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

        if (isset($requestData['new_comment'])) {
            $materialOperationComment = new q3wOperationComment([
                'material_operation_id' => $materialOperation->id,
                'operation_route_stage_id' => $materialOperation->operation_route_stage_id,
                'comment' => $requestData['new_comment']
            ]);

            $materialOperationComment->save();
        }
        foreach ($requestData['materials'] as $inputMaterial) {
            $materialStandard = q3wMaterialStandard::findOrFail($inputMaterial['standard_id']);
            $materialType = $materialStandard->materialType;

            $inputMaterialAmount = $inputMaterial['amount'];
            $inputMaterialQuantity = $inputMaterial['quantity'];


            $operationMaterial = new q3wOperationMaterial([
                'material_operation_id' => $materialOperation->id,
                'standard_id' => $materialStandard->id,
                'amount' => $inputMaterialAmount,
                'quantity' => $inputMaterialQuantity
            ]);

            $operationMaterial->save();

            if ($materialType->accounting_type == 2) {
                $material = q3wMaterial::where('project_object', $requestData['project_object_id'])
                    ->where('standard_id', $materialStandard->id)
                    ->where('quantity', $inputMaterialQuantity)
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
                $material = new q3wMaterial([
                    'standard_id' => $materialStandard->id,
                    'project_object' => $requestData['project_object_id'],
                    'amount' => $inputMaterialAmount,
                    'quantity' => $inputMaterialQuantity
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
            'result' => 'ok',
            'key' => $materialStandard->id
        ], 200);
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
