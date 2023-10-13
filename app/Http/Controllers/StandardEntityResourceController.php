<?php

namespace App\Http\Controllers;

use App\Services\Common\FilesUploadService;
use App\Services\Common\FileSystemService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StandardEntityResourceController extends Controller
{
    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $baseBladePath;
    protected $componentsPath;
    protected $components;

    
    public function getPageCore() 
    {
        return view('1_base.desktop.index',
        [
            'routeNameFixedPart' => $this->routeNameFixedPart,
            'sectionTitle' => $this->sectionTitle, 
            'baseBladePath' => $this->baseBladePath, 
            'components' => $this->components
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
            ->orderBy('id', 'desc')
            ->get();

        if(!empty($options->group)) {
            $groups = $this->handleGroupResponse($entities, $options->group);
            return json_encode(array(
                    "data" => $groups['data'],
                    "groupCount" => $groups['groupCount'],
                    "totalCount" => $groups['totalCount'],
                ),
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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
            $ignoreDataKeys = $beforeStoreResult['ignoreDataKeys'];
            $dataToStore = $this->getDataToStore($data, $ignoreDataKeys);
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
            $ignoreDataKeys = $beforeUpdateResult['ignoreDataKeys'];
            $dataToUpdate = $this->getDataToStore($data, $ignoreDataKeys);
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
        $ignoreDataKeys = [];

        if(!empty($data['newAttachments']))
            $ignoreDataKeys[] = 'newAttachments';

        return [
            'data' => $data, 
            'ignoreDataKeys' => ['newAttachments']
        ];
    }

    public function afterStore($entity, $data)
    {
        if(!empty($data['newAttachments']))
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);
    }

    public function beforeUpdate($entity, $data)
    {
        $ignoreDataKeys = [];

        if(!empty($data['newAttachments']))
            $ignoreDataKeys[] = 'newAttachments';

        return [
            'data' => $data, 
            'ignoreDataKeys' => ['newAttachments']
        ];
    }

    public function afterUpdate($entity, $data)
    {
        if(!empty($data['newAttachments']))
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);
    }

    public function beforeDelete($entity)
    {
       //
    }

    public function afterDelete($entity)
    {
       //
    }

    public function getDataToStore($data, $ignoreDataKeys)
    {
        if(empty($ignoreDataKeys))
        return $data;

        $dataToStore = [];
        foreach($data as $key=>$value){
            if(!in_array($key, $ignoreDataKeys)) 
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

    public function handleGroupResponse($entities, $groupRequest)
    {
        foreach($groupRequest as $groupRequestElement){
            $groupBy = $groupRequestElement->selector;
            $groupByArr = $entities->pluck($groupBy)->unique();
    
            $groups = [];
            $groups['groupCount'] = 0;
            $groups['totalCount'] = $entities->count();
    
            foreach($groupByArr as $groupKey) {
                $projectObjectDocumentsGrouped = $entities->where($groupBy, $groupKey);
                $groupData = new \stdClass;
                $groupData->key = $groupKey;
                $groupData->count = $projectObjectDocumentsGrouped->count();
                $groupData->items = null;
                $groupData->summary = [];
                $groups['data'][] = $groupData;
                ++ $groups['groupCount'];
            }
        }
       
        return $groups;
    }

}
