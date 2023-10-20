<?php

namespace App\Http\Controllers\ProjectObjectDocuments;

use App\Events\NotificationCreated;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Comment;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\ProjectObjectDocuments\ProjectObjectDocument;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentsStatusType;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatusOptions;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatusTypeRelation;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentType;
use App\Models\User;
use App\Services\Common\FileSystemService;
use App\Services\ProjectObjectDocuments\Reports\ProjectObjectDocumentsXLSXReport;
use App\Services\ProjectObjectDocuments\Reports\TestDownload;
use App\Services\SystemService;
use Carbon\Carbon;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProjectObjectDocumentsController extends Controller
{
    private $commentElems;
    private $components;

    public function __construct ($commentElems = [], $components = [])
    {
        $this->commentElems = $commentElems;
        $this->components = $components;
    }

    public function returnPageCore() {
        $clientDeviceType = SystemService::determineClientDeviceType($_SERVER["HTTP_USER_AGENT"]);

        if($clientDeviceType === 'desktop')
        return view('project_object_documents.desktop.index');

        $basePath = resource_path().'/views/project_object_documents';
        $componentsPath = resource_path().'/views/project_object_documents/mobile/components';
        $components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($componentsPath, $basePath);
        return view('project_object_documents.mobile.index', compact('components'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = json_decode($request['data']);
        unset($options->take);

        //Если кастомные фильтры применять совместно с фильтрами devexpress
        //через механизм laravel when()->where()
        //в некоторых случаях срабатывает в запросе OR вместо AND
        //поэтому подготавливаем options для применения всех фильтров внутри трейта DevExtremeDataSourceLoadable

        if(json_decode($request['projectObjectsFilter']))
        $options = $this->addCustomFilters(
            ['project_object_id'],
            json_decode($request['projectObjectsFilter']),
            $options
        );

        if(json_decode($request['projectResponsiblesFilter']))
        {
            $projectResponsiblesFilter = json_decode($request['projectResponsiblesFilter']);
            $objects = ObjectResponsibleUser::whereIn('user_id', $projectResponsiblesFilter);

            if(!$objects->exists())
            return json_encode(array(
                "data" => [],
                ),
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

            $objectsIds = $objects->pluck('object_id')->toArray();
            $options = $this->addCustomFilters(
                ['project_object_id'],
                $objectsIds,
                $options
            );
        }

        $documentArchivedOrDeletedStatusesIds = $this->getDocumentArchivedOrDeletedStatusesIds();

        $projectObjectDocuments =
            $this->getProjectObjectDocumentsList($options)
            ->when(str_contains($request->customSearchParams, 'showArchive=1'), function($query) use($documentArchivedOrDeletedStatusesIds) {
                return $query->whereIn('document_status_id', $documentArchivedOrDeletedStatusesIds )->withTrashed();
            })
            ->when(!str_contains($request->customSearchParams, 'showArchive=1'), function($query) use($documentArchivedOrDeletedStatusesIds) {
                return $query->whereNotIn('document_status_id', $documentArchivedOrDeletedStatusesIds );
            })
            ->orderByDesc('id')
            ->get();

        if(!empty($options->group)) {
            $groups = $this->handleGroupResponse($projectObjectDocuments, $options->group);
            return json_encode(array(
                    "data" => $groups['data'],
                    "groupCount" => $groups['groupCount'],
                    "totalCount" => $groups['totalCount'],
                ),
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }

        return json_encode(array(
                "data" => $projectObjectDocuments,
            ),
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function getDocumentArchivedOrDeletedStatusesIds() {
        return ProjectObjectDocumentsStatusType::where('slug', 'document_archived_or_deleted')
            ->first()
            ->projectObjectDocumentStatuses
            ->pluck('id')
            ->toArray();
    }

    public function indexMobile(Request $request)
    {
        $options = json_decode($request['data']);
        unset($options->take);

        if(json_decode($request['projectResponsiblesFilter']))
        {
            $projectResponsiblesFilter = json_decode($request['projectResponsiblesFilter']);
            $objects = ObjectResponsibleUser::whereIn('user_id', $projectResponsiblesFilter);

            if(!$objects->exists())
            return json_encode(array(
                "data" => [],
                ),
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

            $objectsIds = $objects->pluck('object_id')->toArray();
            $options = $this->addCustomFilters(
                ['project_object_id'],
                $objectsIds,
                $options
            );
        }

        $documentArchivedOrDeletedStatusesIds = $this->getDocumentArchivedOrDeletedStatusesIds();

        $projectObjectDocuments =
            $this->getProjectObjectDocumentsList($options)
            ->whereNotIn('document_status_id', $documentArchivedOrDeletedStatusesIds)
            ->orderByDesc('id')
            ->get();

        return $projectObjectDocuments->toJSON(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function getProjectObjectDocumentsList($options)
    {
        return (new ProjectObjectDocument)
        ->dxLoadOptions($options)
        ->leftJoin('project_object_document_types', 'project_object_document_types.id', '=', 'project_object_documents.document_type_id')
        ->leftJoin('project_objects', 'project_objects.id', '=', 'project_object_documents.project_object_id')
        ->addSelect('project_object_documents.*')
        ->addSelect('project_objects.short_name as project_object_short_name' )
        ->addSelect('project_object_documents.id as project_object_documents_id')
        ->addSelect('project_object_document_types.sortOrder AS sortOrder')
        ->orderBy('sortOrder')
        ->with([
            'projectObject:id,short_name',
            'type',
            'status.projectObjectDocumentsStatusType',
        ]);
    }

    public function handleGroupResponse($projectObjectDocuments, $groupRequest)
    {
        $groupBy = $groupRequest[0]->selector;
        $groupByArr = $projectObjectDocuments->pluck($groupBy)->unique();

        $groups = [];
        $groups['groupCount'] = 0;
        $groups['totalCount'] = $projectObjectDocuments->count();
        $redStatusesIds = ProjectObjectDocumentStatus::where('status_type_id', 1)->pluck('id')->toArray();
        $orangeStatusesIds = ProjectObjectDocumentStatus::where('status_type_id', 2)->pluck('id')->toArray();
        $greenStatusesIds = ProjectObjectDocumentStatus::where('status_type_id', 3)->pluck('id')->toArray();
        $greyStatusesIds = ProjectObjectDocumentStatus::where('status_type_id', 4)->pluck('id')->toArray();

        foreach($groupByArr as $groupKey) {
            $projectObjectDocumentsGrouped = $projectObjectDocuments->where($groupBy, $groupKey);
            $groupData = new \stdClass;
            $groupData->key = $groupKey;
            $groupData->count = $projectObjectDocumentsGrouped->count();
            $groupData->items = null;
            $groupData->summary = [
                'red' => $projectObjectDocuments->whereIn('document_status_id', $redStatusesIds)->where($groupBy, $groupKey)->count(),
                'orange' => $projectObjectDocuments->whereIn('document_status_id', $orangeStatusesIds)->where($groupBy, $groupKey)->count(),
                'green' => $projectObjectDocuments->whereIn('document_status_id', $greenStatusesIds)->where($groupBy, $groupKey)->count(),
                'grey' => $projectObjectDocuments->whereIn('document_status_id', $greenStatusesIds)->where($groupBy, $groupKey)->count(),
            ];
            $groups['data'][] = $groupData;
            ++ $groups['groupCount'];
        }

        return $groups;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = json_decode($request->input('data'));

        DB::beginTransaction();

        $id = ProjectObjectDocument::insertGetId(
            [
                'document_type_id' => $data->document_type_id,
                'document_status_id' => $data->document_status_id,
                'project_object_id' => $data->project_object_id,
                'author_id' => Auth::user()->id,
                'document_name' => $data->document_name,
                'document_date' => $data->document_date  ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->syncCommentsAndFiles($data, $id);
        $this->addDataToActionLog('store', $data, $id);

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'id' => $id
        ], 200);
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
        $data = json_decode($request->input('data'));
        $toUpdateArr = $this->getDataToUpdate($data);

        DB::beginTransaction();

        ProjectObjectDocument::findOrFail($id)->update($toUpdateArr);

        $this->syncCommentsAndFiles($data, $id);
        $this->addDataToActionLog('update', $data, $id);

        if(!empty($toUpdateArr['document_status_id']))
        $this->notifyResponsiblesAboutNewDocumentStatus($id, $toUpdateArr['document_status_id']);

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'id' => $id
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $document = ProjectObjectDocument::find($id);

        $deletedDocumentStatusId = ProjectObjectDocumentStatus::where('name', 'Удален')->first()->id;

        $document->update([
            'document_status_id' => $deletedDocumentStatusId
        ]);

        $document->delete();

        $this->addDataToActionLog('delete', ['document_status_id'=>$deletedDocumentStatusId], $id);
        (new ProjectObjectDocumentsController(['Документ удален']))->addComment($id);

        return response()->json([
            'result' => 'ok',
            'id' => $id
        ], 200);
    }

    public function restoreDocument($id)
    {
        $document = ProjectObjectDocument::withTrashed()->find($id);
        $document->restore();
        $lastDocumentActiveStatusId = $this->getLastDocumentActiveStatusId($id);
        $document->update([
            'document_status_id' => $lastDocumentActiveStatusId
        ]);

        $this->addDataToActionLog('restore', ['document_status_id'=>$lastDocumentActiveStatusId], $id);
        (new ProjectObjectDocumentsController(['Документ восстановлен']))->addComment($id);

        return response()->json([
            'result' => 'ok',
            'document' => $document
        ], 200);
    }

    public function getLastDocumentActiveStatusId($id)
    {
        $actionLog = ActionLog::where([
                ['logable_id', $id],
                ['logable_type', 'App\Models\ProjectObjectDocuments\ProjectObjectDocument'],
                ['actions', 'LIKE', '%document_status_id%'],
                ['actions', 'NOT LIKE', '%"event":"delete"%'],
                ['actions', 'NOT LIKE', '%"event":"archive"%'],
            ])->orderBy('id', 'desc')->first();

        if($actionLog )
            $statusId = $actionLog->actions['new_values']['document_status_id'];
        else {
            // Если записей в action_logs не найдено восстанавливаем со статусом по умолчанию
            $document_type_id = ProjectObjectDocument::find($id)->document_type_id;
            $statusId = ProjectObjectDocumentStatusTypeRelation::query()
                ->where([
                    ['document_type_id', $document_type_id],
                    ['default_selection', 1]
                ])->first()->document_status_id;
        }

        return $statusId;
    }

    public function addCustomFilters($keysArr, $filterArr, $options)
    {
        if(empty($options->filter))
        $options->filter = [];

        if(count($options->filter) && count($filterArr)){
            $options->filter = [$options->filter];
            $options->filter[] = 'and';
        }

        $newFilterParams = [];
        $i = 0;
        $filterArrLength = count($filterArr);
        $keysArrLength = count($keysArr);

        foreach($filterArr as $filterElem){
            $n = 0;
            foreach($keysArr as $key){
                $newFilterParams[] = [$key, '=', $filterElem];
                if(++$i < $filterArrLength || ++$n < $keysArrLength)
                $newFilterParams[] = 'or';
            }
        }

        $options->filter[] = $newFilterParams;

        return $options;
    }

    public function notifyResponsiblesAboutNewDocumentStatus($id, $newStatusId)
    {
        $document = ProjectObjectDocument::findOrFail($id);

        $rolesIds = ObjectResponsibleUserRole::whereIn('slug', ['TONGUE_PTO_ENGINEER'])->pluck('id')->toArray();

        $notificationRecipients =
            ObjectResponsibleUser::query()
                ->where('object_id', $document->project_object_id)
                ->whereIn('object_responsible_user_role_id', $rolesIds)
                ->whereNotIn('user_id', [Auth::user()->id])
                ->pluck('user_id');

        $objectName = ProjectObject::findOrFail($document->project_object_id)->short_name;

        $statusName = ProjectObjectDocumentStatus::find($document->document_status_id)->name;

        foreach($notificationRecipients as $userId)
        {
            $notification = Notification::withoutEvents(function() use($objectName, $userId, $document, $statusName) {

                    $notification = Notification::create([
                        'name' => 'Документооборот на объектах | ' . "\n" . $objectName. ' | ' . "\n" . $document->document_name . ' | '. "\n" . 'Новый статус: ' . $statusName,
                        'user_id' => $userId,
                        'type' => 0,
                    ]);

                    return $notification;
                }
            );

            //
            // event(new NotificationCreated($notification->name, $notification->user_id, $notification->type, $notification->id));
        }
    }

    public function getDataToUpdate($data)
    {
        $toUpdateArr = [];

        if(!empty($data->document_type_id))
        $toUpdateArr['document_type_id'] = $data->document_type_id;
        if(!empty($data->document_status_id))
        $toUpdateArr['document_status_id'] = $data->document_status_id;
        if(!empty($data->project_object_id))
        $toUpdateArr['project_object_id'] = $data->project_object_id;
        if(!empty($data->document_name))
        $toUpdateArr['document_name'] = $data->document_name;
        if(!empty($data->document_date))
        $toUpdateArr['document_date'] = $data->document_date;

        return $toUpdateArr;
    }

    public function syncCommentsAndFiles($data, $id)
    {
        if(!empty($data->project_object_id)){
            $newObjectName = ProjectObject::find($data->project_object_id)->short_name;
            $this->commentElems[] = 'Новый объект: '.$newObjectName;
        }

        // if(!empty($data->document_status_id)){
        //     $newStatus = ProjectObjectDocumentStatus::find($data->document_status_id)->name;
        //     $this->commentElems[] = 'Новый статус: '.$newStatus;
        // }
        if(!empty($data->newCommentsArr)){
            foreach($data->newCommentsArr as $comment)
            $this->commentElems[] = $comment->comment;
        }

        if(!empty($data->typeStatusOptions)){
            $this->handleProjectObjectDocumentOptions($data, $id);
        }

        if(!empty($data->new_comment))
        $this->commentElems[] = $data->new_comment;

        if(!empty($data->newAttachments))
        $this->attachFiles($data->newAttachments, $id);

        if(!empty($data->deletedAttachments))
        $this->deleteFiles($data->deletedAttachments);

        if(count($this->commentElems))
        $this->addComment($id);
    }

    public function handleProjectObjectDocumentOptions($data, $id)
    {
        $projectObjectDocumentOptions = [];

        foreach($data->typeStatusOptions as $option){

            $projectObjectDocumentOptions[$option->id] = $option;

            if ($option->type == 'select') {
                $recievedBy = User::find($option->value)->full_name;
                // $this->addComment($option->comment.': '.$recievedBy, $id);
                $this->commentElems[] = $option->comment.': '.$recievedBy;
            }
            elseif ($option->type == 'checkbox') {
                if($option->value)
                $this->commentElems[] = 'Указано: '.$option->comment;
                // $this->addComment('Указано: '.$option->comment, $id);
                else
                $this->commentElems[] = 'Снята галка: '.$option->comment;
                // $this->addComment('Снята галка: '.$option->comment, $id);
            }
            elseif ($option->type == 'text') {
                $this->commentElems[] = 'Указано: '.$option->comment.': '.$option->value;
                // $this->addComment('Указано: '.$option->comment.': '.$option->value, $id);
            }
        }

        $projectObjectDocumentOptionsCurrentValue = json_decode(ProjectObjectDocument::find($id)->options);

        if($projectObjectDocumentOptionsCurrentValue) {
            $newOptionsKeys = array_keys($projectObjectDocumentOptions);
            foreach($projectObjectDocumentOptionsCurrentValue as $key=>$value){
                if(!in_array($key, $newOptionsKeys))
                $projectObjectDocumentOptions[$key] = $value;
            }
        }

        ProjectObjectDocument::find($id)->update([
            'options' => json_encode($projectObjectDocumentOptions)
        ]);
    }

    public function addComment($id)
    {
        $comment = '';
        for ($i=0; $i < count($this->commentElems); $i++) {
            if($i>0)
            $comment = $comment.' | ';
            $comment = $comment.$this->commentElems[$i];
        }

        Comment::create([
            'commentable_id' => $id,
            'commentable_type' => 'App\Models\ProjectObjectDocuments\ProjectObjectDocument',
            'comment' => $comment,
            'author_id' => Auth::user()->id,

        ]);
    }

    public function attachFiles($newAttachments, $id)
    {
        foreach($newAttachments as $fileId)
        FileEntry::find($fileId)->update(['documentable_id' => $id]);
    }

    public function deleteFiles($deletedAttachments)
    {
        foreach($deletedAttachments as $fileId){
            $fileEntry = FileEntry::find($fileId);
            if($fileEntry){
                $fileEntry->documentable_id = NULL;
                $fileEntry->save();
            }
            // Storage::disk('project_object_documents')->delete($fileEntry->filename);
            // $fileEntry->delete();
        }

    }


    public function addDataToActionLog($event, $data, $id)
    {
        $actions = new \stdClass;
        $actions->event = $event;
        $actions->new_values = $data;

        ActionLog::create([
            'logable_id' => $id,
            'logable_type' => 'App\Models\ProjectObjectDocuments\ProjectObjectDocument',
            'actions' => $actions,
            'user_id' => Auth::user()->id,
        ]);
    }

    public function getTypes()
    {
        $types = ProjectObjectDocumentType::with('projectObjectDocumentStatusTypeRelations')->orderBy('sortOrder')->get();
        return response()->json($types, 200);
    }

    public function getStatuses(Request $request)
    {
        // if(!$request->documentTypeId)
        //     $statuses = ProjectObjectDocumentStatus::with('projectObjectDocumentsStatusType')->orderBy('sortOrder')->get();
        // else {
        //     $typeStatuses = ProjectObjectDocumentStatusTypeRelation::where('document_type_id', $request->documentTypeId)
        //         ->pluck('document_status_id')->toArray();
        //     $statuses = ProjectObjectDocumentStatus::with('projectObjectDocumentsStatusType')->whereIn('id', $typeStatuses)->orderBy('sortOrder')->get();
        // }
        $documentArchivedOrDeletedStatusesIds = $this->getDocumentArchivedOrDeletedStatusesIds();

        $statuses =
            ProjectObjectDocumentStatus::with('projectObjectDocumentsStatusType')
            ->when($request->documentTypeId, function($query) use($request){
                $typeStatuses = ProjectObjectDocumentStatusTypeRelation::query()
                ->where('document_type_id', $request->documentTypeId)->pluck('document_status_id')->toArray();

                return $query->whereIn('id', $typeStatuses);
            })
            ->when(str_contains($request->customSearchParams, 'showArchive=1'), function($query) use($documentArchivedOrDeletedStatusesIds) {
                return $query->whereIn('id', $documentArchivedOrDeletedStatusesIds);
            })
            ->when(!str_contains($request->customSearchParams, 'showArchive=1'), function($query) use($documentArchivedOrDeletedStatusesIds) {
                return $query->whereNotIn('id', $documentArchivedOrDeletedStatusesIds);
            })
            ->orderBy('sortOrder')->get();

        // if(!$this->checkCanInteractWithGreenStatus())
        // $statuses = $statuses->where('style', '!=', 'green');

        return response()->json($statuses, 200);
    }

    public function getOptionsByTypeAndStatus(Request $request)
    {
        $optionsCollection = ProjectObjectDocumentStatusOptions::where([
                ['document_type_id', $request->documentTypeId],
                ['document_status_id', $request->statusId]])
            ->orWhere([
                ['document_type_id', $request->documentTypeId],
                ['document_status_id', NULL]])
            ->get();

        $options = [];
        foreach($optionsCollection as $arrJson)
        {
            $arrJsonOptions = json_decode($arrJson->options);
            foreach($arrJsonOptions as $option)
            $options[] = $option;
        }

        return response()->json(json_encode($options), 200);
    }

    public function checkCanInteractWithGreenStatus()
    {
        $canInteractGreenStatus = User::query()->whereNotNull('is_deleted')
            ->whereIn('group_id', Group::PTO)
            ->orWhereIn('group_id', Group::PROJECT_MANAGERS)
            ->pluck('id')->toArray();

        return
            in_array(Auth::user()->id, $canInteractGreenStatus) || Auth::user()->is_su
            ? true
            : false;
    }

    public function getProjectObjects(Request $request)
    {
        $isArchived = filter_var($request->query('is-archived', 'false'), FILTER_VALIDATE_BOOLEAN);
        $archivedStatusTypeCondition = $isArchived ? 'project_object_document_statuses.status_type_id = 4' : 'project_object_document_statuses.status_type_id <> 4';
        $objects = ProjectObject::query()
            ->withResponsibleUserNames()
            ->whereNotNull('short_name')
            ->whereRaw('exists(select * from `project_object_documents` where `project_object_documents`.`project_object_id` = `project_objects`.`id` and `project_object_documents`.`document_status_id` in (select `id` from `project_object_document_statuses` where '.$archivedStatusTypeCondition.'))')
            ->addSelect(['project_objects.id AS id',
                'short_name', 'project_objects.name AS object_name'
                ])
            ->orderBy('short_name')->get();

        return response()->json($objects, 200, [], JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK);
    }

    public function getResponsibles(Request $request)
    {
        if($request->type == 'all'){
            return User::query()->active()
                ->whereIn('group_id', Group::PTO)
                ->orWhereIn('group_id', Group::FOREMEN)
                ->orWhereIn('group_id', Group::PROJECT_MANAGERS)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
        }

        if($request->type == 'managers_and_pto'){
            return User::query()->active()
                ->whereIn('group_id', Group::PTO)
                ->orWhereIn('group_id', Group::PROJECT_MANAGERS)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
        }

        if($request->type == 'managers_and_foremen'){
            return User::query()->active()
                ->orWhereIn('group_id', Group::FOREMEN)
                ->orWhereIn('group_id', Group::PROJECT_MANAGERS)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
        }

        $objectId = ProjectObjectDocument::findOrFail($request->id)->project_object_id;
        $objectResponsibles = ObjectResponsibleUser::where('object_id', $objectId);

        if($request->type == 'manager')
            $responsiblesIds =
                $objectResponsibles
                    ->where('object_responsible_user_role_id', (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'))
                    ->pluck('user_id')
                    ->toArray();

        if($request->type == 'pto')
            $responsiblesIds =
                $objectResponsibles
                    ->where('object_responsible_user_role_id', (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PTO_ENGINEER'))
                    ->pluck('user_id')
                    ->toArray();

        if($request->type == 'foreman')
            $responsiblesIds =
                $objectResponsibles
                    ->where('object_responsible_user_role_id', (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_FOREMAN'))
                    ->pluck('user_id')
                    ->toArray();

        $responsibles = User::whereIn('id', $responsiblesIds)->active()
            ->select(['id', 'first_name', 'last_name', 'patronymic'])
            ->orderBy('last_name')
            ->get();

        return response()->json($responsibles, 200);

    }

    public function getProjectObjectDocumentComments(Request $request)
    {
        if(!$request->input('id'))
        return response()->json([], 200);

        $comments = Comment::where([
                ['commentable_type', 'App\Models\ProjectObjectDocuments\ProjectObjectDocument'],
                ['commentable_id', $request->input('id')]
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json($comments, 200);
    }

    public function getProjectObjectDocumentAttachments(Request $request)
    {
        if(!$request->input('id'))
        return response()->json([], 200);

        $attachments = FileEntry::where([
            ['documentable_type', 'App\Models\ProjectObjectDocuments\ProjectObjectDocument'],
            ['documentable_id', $request->input('id')]
        ])->with('author')
        ->orderByDesc('id')
        ->get();

        $groupedAttachments = [];
        foreach ($attachments as $attachment) {
            $createdAtDate = Carbon::parse($attachment->updated_at)->format('d.m.Y H:i');
            $authorFio = $attachment['author']['full_name'];
            $groupName = $createdAtDate.' ('.$authorFio.')';
            $groupedAttachments[$groupName][] = $attachment;
        }

        return response()->json($groupedAttachments, 200);
    }

    public function uploadFiles(Request $request)
    {
        $uploadedFile = $request->files->all()['files'][0];

        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $fileName =  'file-' . uniqid() . '.' . $fileExtension;

        Storage::disk('project_object_documents')->put($fileName, File::get($uploadedFile));

        $fileEntry = FileEntry::create([
            'filename' => 'storage/docs/project_object_documents/' . $fileName,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getClientMimeType(),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'user_id' => Auth::user()->id,
            'documentable_id' => $request->input('id'),
            'documentable_type' => 'App\Models\ProjectObjectDocuments\ProjectObjectDocument'
        ]);

        return response()->json([
            'result' => 'ok',
            'fileEntryId' => $fileEntry->id,
            'filename' =>  $fileName
        ], 200);
    }

    public function uploadFile(Request $request)
    {
        $uploadedFile = $request->files->all()['files'];

        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $fileName =  'file-' . uniqid() . '.' . $fileExtension;

        Storage::disk('project_object_documents')->put($fileName, File::get($uploadedFile));

        $fileEntry = FileEntry::create([
            'filename' => 'storage/docs/project_object_documents/' . $fileName,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getClientMimeType(),
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'user_id' => Auth::user()->id,
            'documentable_id' => $request->input('id'),
            'documentable_type' => 'App\Models\ProjectObjectDocuments\ProjectObjectDocument'
        ]);

        return response()->json([
            'result' => 'ok',
            'fileEntryId' => $fileEntry->id
        ], 200);

    }

    public function cloneDocument(Request $request)
    {
        $document = ProjectObjectDocument::findOrFail($request->id);

        $newDocument = ProjectObjectDocument::create([
            'document_type_id' => $document->document_type_id,
            'document_status_id' => 1,
            'project_object_id' => $document->project_object_id,
            'author_id' => Auth::user()->id,
            'document_name' => $document->document_name,
        ]);

        return response()->json([
            'result' => 'ok',
            'newDocument' => $newDocument
        ], 200);
    }

    public function downloadXls(Request $request)
    {
        $filterOptions = (object)json_decode($request['filterOptions']);
        $projectObjectsFilter = (array) json_decode($request['projectObjectsFilter']);
        $projectResponsiblesFilter = (array) json_decode($request['projectResponsiblesFilter']);

        if($projectObjectsFilter)
        $options = $this->addCustomFilters(
            ['project_object_id'],
            $projectObjectsFilter,
            $filterOptions
        );

        if($projectResponsiblesFilter)
        {
            $objects = ObjectResponsibleUser::whereIn('user_id', $projectResponsiblesFilter);

            if(!$objects->exists())
            return json_encode(array(
                "data" => [],
                ),
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

            $objectsIds = $objects->pluck('object_id')->toArray();
            $options = $this->addCustomFilters(
                ['project_object_id'],
                $objectsIds,
                $filterOptions
            );
        }

        $documentArchivedOrDeletedStatusesIds = $this->getDocumentArchivedOrDeletedStatusesIds();

        $projectObjectDocuments = (new ProjectObjectDocument)
            ->dxLoadOptions($filterOptions)
            ->when(str_contains($request->customSearchParams, 'showArchive=1'), function($query) use($documentArchivedOrDeletedStatusesIds) {
                return $query->whereIn('document_status_id', $documentArchivedOrDeletedStatusesIds )->withTrashed();
            })
            ->when(!str_contains($request->customSearchParams, 'showArchive=1'), function($query) use($documentArchivedOrDeletedStatusesIds) {
                return $query->whereNotIn('document_status_id', $documentArchivedOrDeletedStatusesIds );
            })
            ->leftJoin('project_object_document_types', 'project_object_document_types.id', '=', 'project_object_documents.document_type_id')
            ->leftJoin('action_logs', 'action_logs.logable_id', '=', 'project_object_documents.id')
            ->leftJoin('project_objects', 'project_objects.id', '=', 'project_object_documents.project_object_id')
            ->leftJoin('object_responsible_users', 'project_objects.id', '=', 'object_responsible_users.object_id')
            ->leftJoin('users', 'users.id', '=', 'object_responsible_users.user_id')
            ->leftJoin('object_responsible_user_roles', 'object_responsible_user_roles.id', '=', 'object_responsible_users.object_responsible_user_role_id')
            ->where('logable_type', 'App\Models\ProjectObjectDocuments\ProjectObjectDocument')
            ->where('actions', 'LIKE' , '%document_status_id%')
            ->addSelect(DB::raw('DISTINCT project_object_documents.*'))
            ->addSelect('project_object_document_types.sortOrder AS sortOrder')
            ->addSelect(DB::raw('MAX(action_logs.created_at) over (PARTITION BY project_object_documents.id) as status_updated_at'))
            ->addSelect('project_object_documents.id as project_object_documents_id')
            ->addSelect([
                DB::raw("GROUP_CONCAT(DISTINCT CASE WHEN `object_responsible_user_roles`.`slug` = 'TONGUE_PROJECT_MANAGER' THEN `users`.`user_full_name` ELSE NULL END ORDER BY `users`.`user_full_name` ASC SEPARATOR ', ' ) AS `tongue_project_manager_full_names`"),
                DB::raw("GROUP_CONCAT(DISTINCT CASE WHEN `object_responsible_user_roles`.`slug` = 'TONGUE_PTO_ENGINEER' THEN `users`.`user_full_name` ELSE NULL END ORDER BY `users`.`user_full_name` ASC SEPARATOR ', ' ) AS `tongue_pto_engineer_full_names`"),
                DB::raw("GROUP_CONCAT(DISTINCT CASE WHEN `object_responsible_user_roles`.`slug` = 'TONGUE_FOREMAN' THEN `users`.`user_full_name` ELSE NULL END ORDER BY `users`.`user_full_name` ASC SEPARATOR ', ' ) AS `tongue_foreman_full_names`")
            ])
            ->groupBy(['project_object_documents.id'])
            ->orderBy('project_objects.short_name')
            ->orderBy('sortOrder')
            ->with([
                'projectObject:id,short_name',
                'type',
                'status.projectObjectDocumentsStatusType',
            ])
            ->get()->toArray();

        return (new ProjectObjectDocumentsXLSXReport($projectObjectDocuments))->export();

    }

    public function getProjectObjectDocumentInfoByID(Request $request)
    {
        $response =  [[
            'comments' => $this->getProjectObjectDocumentComments($request),
            'attachments' => $this->getProjectObjectDocumentAttachments($request),
        ]];

        return response()->json($response, 200);
    }

    public function getDataForLookupsAndFilters() {

        $response =  [[
            'projecObjects' => ProjectObject::query()
                ->whereNotNull('short_name')
                ->where('is_participates_in_documents_flow', '=', 1)
                ->orderBy('short_name')->get(),
            'projecObjectsTypes' => ProjectObjectDocumentType::all(),
            'projecObjectsStatuses' => ProjectObjectDocumentStatus::with('projectObjectDocumentsStatusType')->orderBy('sortOrder')->get(),
            'projecObjectsResponsibles' => User::query()->active()
                ->whereIn('group_id', Group::PTO)
                ->orWhereIn('group_id', Group::FOREMEN)
                ->orWhereIn('group_id', Group::PROJECT_MANAGERS)
                ->select(['id', 'first_name', 'last_name', 'patronymic'])
                ->orderBy('last_name')
                ->get()
        ]];

        return response()->json($response, 200);
    }

    public function downloadAttachments(Request $request)
    {
        if(!count($request->fliesIds))
        return response()->json('no files recieved', 200);

        $storagePath = config('filesystems.disks')['project_object_documents']['root'];

        $zip = new ZipArchive();
        $zipFileName = "file-". uniqid(). "-" . "archive.zip";
        $zipFilePath = $storagePath."/".$zipFileName ;
        $zip->open($zipFilePath, ZIPARCHIVE::CREATE);

        foreach($request->fliesIds as $fileId)
        {
            $file = FileEntry::find($fileId);
            $filenameElems = explode('/', $file->filename);
            $filename = $filenameElems[count($filenameElems) - 1];
            $zip->addFile('storage/docs/project_object_documents/'.$filename, $file->original_filename);
        }

        $zip->close();

        $response = ['zipFileLink'=>'storage/docs/project_object_documents/'.$zipFileName];
        return response()->json($response, 200);
    }

    public function getPermissions()
    {
        $permissions = (new ProjectObjectDocument())->permissions;
        return response()->json($permissions, 200);
    }

}
