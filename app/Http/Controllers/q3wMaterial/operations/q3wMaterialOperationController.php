<?php

namespace App\Http\Controllers\q3wMaterial\operations;

use App\Models\FileEntry;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationComment;
use App\Models\q3wMaterial\operations\q3wOperationFile;
use App\Models\q3wMaterial\operations\q3wOperationFileType;
use App\Models\q3wMaterial\q3wMaterialStandard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
/**
 * Class q3wMaterialOperationController
 * @property  operation_route_id
 * @package App\Http\Controllers\q3wMaterial\operations
 */
class q3wMaterialOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('materials.operations.all');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request)
    {
        $options = json_decode($request['data']);

        $response = array(
            "data" => (new q3wMaterialOperation)
                ->dxLoadOptions($options)
                ->leftJoin('q3w_operation_route_stages', 'operation_route_stage_id', '=', 'q3w_operation_route_stages.id')
                ->addSelect(['q3w_material_operations.*',
                    'q3w_operation_route_stages.name as operation_route_stage_name',
                    DB::raw('CASE WHEN `q3w_operation_route_stages`.`operation_route_stage_type_id` in (3, 5, 6) THEN 0 ELSE 1 END as route_stage_type_sort_order')
                ])
                ->withMaterialsSummary()
                ->get(),
            "totalCount" => (new q3wMaterialOperation)
                ->dxLoadOptions($options)
                ->leftJoin('q3w_operation_route_stages', 'operation_route_stage_id', '=', 'q3w_operation_route_stages.id')
                ->addSelect('q3w_material_operations.*', 'q3w_operation_route_stages.name as operation_route_stage_name')
                ->count()
        );

        return json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function projectObjectActiveOperations(Request $request)
    {
        $projectObject = ProjectObject::findOrFail($request->projectObjectId);

        return q3wMaterialOperation::where(function ($query) use ($projectObject) {
            $query->where('source_project_object_id', $projectObject->id)
                ->orWhere('destination_project_object_id', $projectObject->id);
        })
            ->onlyActive()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'operation_route_id', 'operation_route_stage_id', 'source_responsible_user_id', 'destination_responsible_user_id', 'source_project_object_id', 'destination_project_object_id'])
            ->toJson(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param q3wMaterialStandard $q3wMaterialStandard
     * @return \Illuminate\Http\Response
     */
    public function edit(q3wMaterialStandard $q3wMaterialStandard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param q3wMaterialStandard $q3wMaterialStandard
     * @return JsonResponse
     */
    public function update(Request $request, q3wMaterialStandard $q3wMaterialStandard)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param q3wMaterialStandard $q3wMaterialStandard
     * @return JsonResponse
     */
    public function delete(Request $request)
    {

    }

    public function uploadAttachedFile(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->all()['files'][0];
        if ($uploadedFile) {
            $file = new q3wOperationFile();

            $fileExtension = $uploadedFile->getClientOriginalExtension();
            $fileName =  'file-' . uniqid() . '.' . $fileExtension;

            Storage::disk('material_operation_files')->put($fileName, File::get($uploadedFile));

            FileEntry::create([
                'filename' => 'storage/docs/material_operation_files/' . $fileName,
                'size' => $uploadedFile->getSize(),
                'mime' => $uploadedFile->getClientMimeType(),
                'original_filename' => $uploadedFile->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $fileName;
            $file->file_path = 'storage/docs/material_operation_files/';
            $file->original_file_name = $uploadedFile->getClientOriginalName();
            $file->user_id = Auth::user()->id;
            $file->upload_file_type = q3wOperationFileType::where('string_identifier', '=', $request->uploadPurpose)->firstOrFail()->id;

            $file->save();
            return response()->json($file);
        }
        return response()->json(null);
    }

    public function print(Request $request) {
        $filterOptions = json_decode($request->input('filterOptions'));
        $filterList = json_decode($request->input('filterList'));

        $operations = (new q3wMaterialOperation)
            ->dxLoadOptions($filterOptions)
            ->leftJoin('q3w_operation_route_stages', 'operation_route_stage_id', '=', 'q3w_operation_route_stages.id')
            ->leftJoin('q3w_operation_routes', 'q3w_material_operations.operation_route_id', '=', 'q3w_operation_routes.id')
            ->withMaterialsSummary()
            ->addSelect(['q3w_material_operations.*',
                'q3w_operation_route_stages.name as operation_route_stage_name',
                'q3w_operation_routes.name as operation_route_name'])
            ->get()
            ->toArray();

        return view('materials.operations.print-all-operations')
            ->with([
                'operations' => $operations,
                'filterList' => $filterList
            ]);
    }

    public function commentHistoryList(Request $request) {
        $operationId = $request['operationId'];

        q3wMaterialOperation::findOrFail($operationId);

        return q3wOperationComment::where('material_operation_id', '=', $operationId)
            ->leftJoin('users', 'q3w_operation_comments.user_id', '=', 'users.id')
            ->leftJoin('q3w_operation_route_stages', 'q3w_operation_comments.operation_route_stage_id', '=', 'q3w_operation_route_stages.id')
            ->orderBy('q3w_operation_comments.created_at', 'desc')
            ->get([
                'q3w_operation_comments.*',
                'q3w_operation_route_stages.name as route_stage_name',
                'users.first_name',
                'users.last_name',
                'users.patronymic',
                'users.image'
            ])
            ->toJSON(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function filesHistoryList(Request $request) {
        $operationId = $request['operationId'];

        q3wMaterialOperation::findOrFail($operationId);

        $operationFiles = q3wOperationFile::where('material_operation_id', '=', $operationId)
            ->leftJoin('users', 'q3w_operation_files.user_id', '=', 'users.id')
            ->leftJoin('q3w_operation_route_stages', 'q3w_operation_files.operation_route_stage_id', '=', 'q3w_operation_route_stages.id')
            ->leftJoin('q3w_operation_file_types', 'q3w_operation_files.upload_file_type', '=', 'q3w_operation_file_types.id')
            ->orderBy('q3w_operation_files.operation_route_stage_id', 'desc')
            ->get([
                'q3w_operation_files.*',
                'q3w_operation_route_stages.name as route_stage_name',
                'q3w_operation_file_types.name as file_type_name',
                'users.first_name',
                'users.last_name',
                'users.patronymic',
                'users.image'
            ])
            ->groupBy('operation_route_stage_id')
            ->toArray();
            //->toJSON(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

            $resultArray = [];

            foreach ($operationFiles as $operationRouteStageId=>$operationFile) {
                $resultElement['operationRouteStageId'] = $operationRouteStageId;
                $resultElement['data'] = $operationFile;

                $resultArray[] = $resultElement;
            }

            return json_encode($resultArray, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
