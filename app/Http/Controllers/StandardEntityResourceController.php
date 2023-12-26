<?php

namespace App\Http\Controllers;

use App\Models\FileEntry;
use App\Models\Permission;
use App\Models\User;
use App\Services\Common\FilesUploadService;
use App\Services\Common\FileSystemService;
use App\Services\SystemService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class StandardEntityResourceController extends Controller
{
    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $baseBladePath;
    protected $componentsPath;
    protected $components;
    protected $ignoreDataKeys;
    protected $modulePermissionsGroups;
    protected $isMobile;
    protected $morphable_type;
    protected $storage_name;
    protected $resources;

    public function __construct()
    {
        $this->ignoreDataKeys = [
            'newAttachments',
            'deletedAttachments',
            'newComments'
        ];

        $this->resources = new \stdClass;
        $this->setResources();
    }
    
    public function getPageCore() 
    {
        $bladePath = '1_base.desktop.index';
        if($this->isMobile) {
            $bladePath = '1_base.mobile.index';
        }
        
        return view($bladePath,
        [
            'routeNameFixedPart' => $this->routeNameFixedPart,
            'sectionTitle' => $this->sectionTitle, 
            'baseBladePath' => $this->baseBladePath, 
            'components' => $this->components,
            'userPermissions' => json_encode($this->getUserPermissions()),
            'resources' => json_encode($this->resources, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = json_decode($request['data']);
        
        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->get();

        if(!empty($options->group)) {
            if(!empty($options->group[0]->selector)){
                $groups = $this->handleGroupResponse($entities, $options->group);
                return json_encode(array(
                        'data' => $groups
                        // "data" => $groups['data'],
                        // "groupCount" => $groups['groupCount'],
                        // "totalCount" => $groups['totalCount'],
                    ),
                    JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            }
        }
    
        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = (array)json_decode($request->input('data'));

        DB::beginTransaction();
            $beforeStoreResult = $this->beforeStore($data);
            $data = $beforeStoreResult['data'];
            // $ignoreDataKeys = $this->ignoreDataKeys;
            $dataToStore = $this->getDataToStore($data);
            $entity = $this->baseModel->create($dataToStore);
            $this->afterStore($entity, $data, $dataToStore);
        DB::commit();

        return json_encode(array(
            "stored" => $entity
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $entity = $this->baseModel::find($id);
        if(!$entity)

        return json_encode([
            'data' => [],
            'comments' => [],
            'attachments' => []
        ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        $resultArr = ['data' => $entity];
        
        if(method_exists($this->baseModel, 'comments'))
            $resultArr['comments'] = $entity->comments;
        if(method_exists($this->baseModel, 'attachments'))
            $resultArr['attachments'] = $this->getGroupedAttachments($entity->attachments);
            
        return json_encode($resultArr, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = (array)json_decode($request->input('data'));
        $entity = $this->baseModel::findOrFail($id);

        DB::beginTransaction();
            $beforeUpdateResult = $this->beforeUpdate($entity, $data);
            $data = $beforeUpdateResult['data'];
            // $ignoreDataKeys = $beforeUpdateResult['ignoreDataKeys'];
            $dataToUpdate = $this->getDataToStore($data);
            $entity->update($dataToUpdate);
            $this->afterUpdate($entity, $data, $dataToUpdate);
        DB::commit();

        return json_encode(array(
            "updated" => $entity
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $entity = $this->baseModel::findOrFail($id);
        DB::beginTransaction();
            $this->beforeDelete($entity);
            $entity->delete();
            $this->afterDelete($entity);
        DB::commit();

        return json_encode(array(
            "deleted" => 'ok'
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function beforeStore($data)
    {
        // $ignoreDataKeys = [];

        // if(!empty($data['newAttachments']))
        //     $ignoreDataKeys[] = 'newAttachments';
        // if(!empty($data['deletedAttachments']))
        //     $ignoreDataKeys[] = 'deletedAttachments';

        return [
            'data' => $data, 
            // 'ignoreDataKeys' => $ignoreDataKeys
        ];
    }

    public function afterStore($entity, $data, $dataToStore)
    {
        if(!empty($data['newAttachments']))
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);

        if(!empty($data['deletedAttachments']))
            $this->deleteFiles($data['deletedAttachments']);
    }

    public function beforeUpdate($entity, $data)
    {            
        return [
            'data' => $data, 
            // 'ignoreDataKeys' => $ignoreDataKeys
        ];
    }

    public function afterUpdate($entity, $data)
    {
        if(!empty($data['newAttachments']))
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);

        if(!empty($data['deletedAttachments']))
            $this->deleteFiles($data['deletedAttachments']);
    }

    public function beforeDelete($entity)
    {
       //
    }

    public function afterDelete($entity)
    {
       //
    }

    public function getDataToStore($data)
    {
        if(empty($this->ignoreDataKeys))
        return $data;

        $dataToStore = [];
        foreach($data as $key=>$value){
            if(!in_array($key, $this->ignoreDataKeys)) 
                $dataToStore[$key] = $value;
        }

        return $dataToStore;
    }

    public function getGroupedAttachments($attachments)
    {
        $groupedAttachments = [];
        foreach ($attachments as $attachment) {
            $createdAtDate = Carbon::parse($attachment->updated_at)->format('d.m.Y H:i');
            $authorFio = $attachment['author']['full_name'];
            $groupName = $createdAtDate.' ('.$authorFio.')';
            $groupedAttachments[$groupName][] = $attachment;
        }

        return $groupedAttachments;
    }

    public function handleGroupResponse($entities, $groupRequest, $groups = [])
    {
        for ($i=0; $i<count($groupRequest); $i++) {

        // foreach($groupRequest as $groupRequestElement){
            // $groupBy = $groupRequestElement->selector;

            $groupBy = $groupRequest[$i]->selector;
            $isSortOrderDesc = $groupRequest[$i]->desc;
            $groupByArr = $entities->pluck($groupBy)->unique()->toArray();
            if($isSortOrderDesc) {
                arsort($groupByArr);
            }
      
            foreach($groupByArr as $groupKey) {
                $projectObjectDocumentsGrouped = $entities->where($groupBy, $groupKey);
                $groupData = new \stdClass;
                $groupData->key = $groupKey;
                $groupData->count = $projectObjectDocumentsGrouped->count();
                $groupData->summary = [];
                if(!isset($groupRequest[$i+1])) {
                    $groupData->items = null;
                } else {
                    $groupData->items = $this->handleGroupResponse($projectObjectDocumentsGrouped, [$groupRequest[$i+1]], $groups);
                }
                $groups[] = $groupData;
            }
        }
       
        return $groups;
    }

    // public function handleGroupResponse($entities, $groupRequest)
    // {
    //     foreach($groupRequest as $groupRequestElement){
    //         $groupBy = $groupRequestElement->selector;
    //         $groupByArr = $entities->pluck($groupBy)->unique();
    
    //         $groups = [];
    //         $groups['groupCount'] = 0;
    //         $groups['totalCount'] = $entities->count();
    
    //         foreach($groupByArr as $groupKey) {
    //             $projectObjectDocumentsGrouped = $entities->where($groupBy, $groupKey);
    //             $groupData = new \stdClass;
    //             $groupData->key = $groupKey;
    //             $groupData->count = $projectObjectDocumentsGrouped->count();
    //             $groupData->items = null;
    //             $groupData->summary = [];
    //             $groups['data'][] = $groupData;
    //             ++ $groups['groupCount'];
    //         }
    //     }
       
    //     return $groups;
    // }

    public function deleteFiles($deletedAttachments)
    {
        foreach($deletedAttachments as $fileId){
            $fileEntry = FileEntry::find($fileId);
            if($fileEntry){
                $fileEntry->documentable_id = NULL;
                $fileEntry->save();
            }
        }

    }

    public function getUserPermissions()
    {
        $permissionsGroups = $this->modulePermissionsGroups ?? null;
        $permissions = empty($permissionsGroups) ? Permission::all() : Permission::whereIn("category", $permissionsGroups)->get();

        foreach ($permissions as $permission){
            $permissionsArray[$permission->codename] = User::find(Auth::user()->id)->can($permission->codename);
        }

        return $permissionsArray;
    }

    public function uploadFile(Request $request)
    {
        $uploadedFile = $request->files->all()['files'][0];
        $documentable_id = $request->input('id');

        [$fileEntry, $fileName] 
            = (new FilesUploadService)
            ->uploadFile($uploadedFile, $documentable_id, $this->morphable_type, $this->storage_name);

        return response()->json([
            'result' => 'ok',
            'fileEntryId' => $fileEntry->id,
            'filename' =>  $fileName,
            'fileEntry' => $fileEntry
        ], 200);
    }

    public function setResources()
    {
        return;
    }

}
