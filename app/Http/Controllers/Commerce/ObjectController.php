<?php

namespace App\Http\Controllers\Commerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectObjectDocuments\ProjectObjectDocumentsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\ProjectObject;
use App\Models\Project;
use App\Models\Building\ObjectResponsibleUser;

use App\Http\Requests\ObjectRequests\ObjectRequest;
use App\Models\ActionLog;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Notification;
use App\Models\ProjectObjectDocuments\ProjectObjectDocument;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatusTypeRelation;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentType;
use Illuminate\Support\Facades\App;

class ObjectController extends Controller
{


    public function index(Request $request)
    {
        $objects = ProjectObject::with('resp_users.user')
            ->with('material_accounting_type')
            ->orderBy('id', 'desc');

        if(Auth::user()->hasLimitMode(0)) {
            $objects->whereHas('resp_users', function ($users) {
                return $users->where('user_id', Auth::id());
            });
        }

        if ($request->search) {
            $objects->getModel()->smartSearch($objects,
                [
                    'name',
                    'address',
                    'short_name',
                    'cadastral_number'
                ],
                $request->search
            );
        }

        $objects_ids = $objects->paginate(15)->pluck('id');

        $projects = Project::getAllProjects()->whereIn('projects.object_id', $objects_ids);

        return view('objects.index', [
            'objects' => $objects->paginate(15),
            'projects' => $projects->get()
        ]);
    }


    public function store(ObjectRequest $request)
    {
        $object = new ProjectObject();

        $object->name = $request->name;
        $object->address = $request->address;
        $object->cadastral_number = $request->cadastral_number;
        $object->short_name = $request->short_name;
        $object->material_accounting_type = $request->material_accounting_type;
        if ($object->is_participates_in_material_accounting) {
            $object->is_participates_in_material_accounting = $request->is_participates_in_material_accounting;
        } else {
            $object->is_participates_in_material_accounting = 0;
        }
        if ($object->is_participates_in_documents_flow) {
            $object->is_participates_in_documents_flow = $request->is_participates_in_documents_flow;
        } else {
            $object->is_participates_in_documents_flow = 0;
        }


        $object->save();

        if($request->task_id) {
            return \GuzzleHttp\json_encode($object->id);
        }

        return redirect()->route('objects::index');
    }


    public function update(ObjectRequest $request)
    {
        DB::beginTransaction();

        $object = ProjectObject::findOrFail($request->object_id);

        $checkedParticipatesInDocumentsFlow = false;
        $uncheckedParticipatesInDocumentsFlow = false;

        $lastObjectResponsibleId = ObjectResponsibleUser::orderByDesc('id')->first()->id;

        if(isset($request->is_participates_in_documents_flow) && !$object->is_participates_in_documents_flow)
        $checkedParticipatesInDocumentsFlow = true; 

        if(!isset($request->is_participates_in_documents_flow) && $object->is_participates_in_documents_flow)
        $uncheckedParticipatesInDocumentsFlow = true; 

        $object->name = $request->name;
        $object->address = $request->address;
        $object->cadastral_number = $request->cadastral_number;
        $object->short_name = $request->short_name;
        $object->material_accounting_type = $request->material_accounting_type;
        if (isset($request->is_participates_in_material_accounting)) {
            $object->is_participates_in_material_accounting = 1;
        } else {
            $object->is_participates_in_material_accounting = 0;
        }
        if (isset($request->is_participates_in_documents_flow)) {
            $object->is_participates_in_documents_flow = 1;
        } else {
            $object->is_participates_in_documents_flow = 0;
        }

        $object->save();

        $this->syncResponsibles($request);

        if($checkedParticipatesInDocumentsFlow){
            $this->handleCheckedParticipatesInDocumentsFlow($request->object_id);
            $this->notifyResponsibleUsers($request->object_id);
        }

        if($uncheckedParticipatesInDocumentsFlow)
            $this->addDocumentsToArchive($request->object_id);

        $this->notifyNewResponsibleUser($request->object_id, $lastObjectResponsibleId);

        DB::commit();

        return redirect()->route('objects::index');
    }

    public function syncResponsibles($request)
    {
        // if(Auth::user()->isProjectManager() or Auth::user()->isInGroup(43)/*8*/) 
        // return;
      
        $rolesArray = [
            'resp_user_role_one' => (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'),
            'resp_user_role_two' => (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PTO_ENGINEER'),
            'resp_user_role_three' => (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_FOREMAN'),
        ];

        $newResponsiblesIds = [];

        foreach($rolesArray as $roleKey=>$roleValue){
            if ($request->$roleKey) {
                foreach ($request->$roleKey as $user_id) {
                    $newResponsible = ObjectResponsibleUser::firstOrCreate([
                        'object_id' => $request->object_id,
                        'user_id' => $user_id,
                        'object_responsible_user_role_id' => $roleValue
                    ]);
                    $newResponsiblesIds[] = $newResponsible->user_id;
                }
            }
        }

        ObjectResponsibleUser::where('object_id', $request->object_id)
            ->whereNotIn('user_id', $newResponsiblesIds)
            ->delete();

        return;
    }

    public function get_object_projects()
    {
        $projects = Project::where('object_id', request()->object_id)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'name' => $project->name,
                'link' => route('projects::card', $project->id),
            ];
        }
        return \GuzzleHttp\json_encode($results);
    }

    public function getObjects(Request $request)
    {
        $objects = ProjectObject::query();
        if ($request->q) {
            $objects = $objects->where(function ($objects) use ($request) {
                $objects->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('address', 'like', '%' . $request->q . '%')
                    ->orWhere('short_name', 'like', '%' . $request->q . '%');
            });
        }
        $objects = $objects->take(10)->get();
        return $objects->map(function ($object) {
            return ['code' => $object->id . '', 'label' => $object->address];
        });
    }

    public function notifyResponsibleUsers($objectId)
    {
        if(!App::environment() != 'production')
        return;

        $notificationRecipients =
            ObjectResponsibleUser::query()
                ->where('object_id', $objectId)
                ->where('user_id', '<>', Auth::user()->id)
                ->pluck('user_id');

        $objectName = ProjectObject::findOrFail($objectId)->short_name;

        foreach($notificationRecipients as $userId)
        {
            Notification::create([
                'name' => 'Документооборот на объектах' . "\n" . $objectName . "\n" . 'Участвует в документообороте',
                'user_id' => $userId,
                'type' => 0,
            ]);
        }
    }

    public function notifyNewResponsibleUser($objectId, $lastObjectResponsibleId)
    {
        $notificationRecipients = ObjectResponsibleUser::where([
            ['id', '>', $lastObjectResponsibleId],
            ['object_id', $objectId],
            ['user_id', '<>', Auth::user()->id]
        ])->pluck('user_id');

        $objectName = ProjectObject::findOrFail($objectId)->short_name;

        foreach($notificationRecipients as $userId)
        {
            Notification::create([
                'name' => 'Вы добавлены ответственным на объект' . "\n" . $objectName,
                'user_id' => $userId,
                'type' => 0,
            ]);
        }
    }

    public function returnDocumentsFromArchive($objectId)
    {
        $archivedObjectDocumentsIds = ProjectObjectDocument::where([
                ['project_object_id', $objectId],
                ['document_status_id', ProjectObjectDocumentStatus::where('name', 'В архиве')->first()->id]
            ])->pluck('id');

        foreach($archivedObjectDocumentsIds as $id) 
            (new ProjectObjectDocumentsController)->restoreDocument($id);
    }

    public function addDocumentsToArchive($objectId)
    {
        $objectDocumentsIds = ProjectObjectDocument::where(
            'project_object_id', $objectId
        )->pluck('id');

        foreach($objectDocumentsIds as $id) 
        {
            $archivedStatusId = ProjectObjectDocumentStatus::where('name', 'В архиве')->first()->id;
            ProjectObjectDocument::find($id)->update([
                'document_status_id' => $archivedStatusId
            ]);

            (new ProjectObjectDocumentsController)->addDataToActionLog('archive', ['document_status_id' => $archivedStatusId], $id);
        }
    }

    public function handleCheckedParticipatesInDocumentsFlow($objectId)
    {
        if(ProjectObjectDocument::where([
            ['project_object_id', $objectId],
            ['document_status_id', ProjectObjectDocumentStatus::where('name', 'В архиве')->first()->id]
        ])->exists())
            $this->returnDocumentsFromArchive($objectId);
        else
            $this->createProjectObjectDocuments($objectId);
    }


    public function createProjectObjectDocuments($objectId)
    {
        $newDocuments = [
            [
                'document_name' => 'ППР',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'ППР')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'ППР')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Акт-допуск на площадку',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Акт с площадки')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Акт с площадки')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Акт передачи фронта работ',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Акт с площадки')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Акт с площадки')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            [
                'document_name' => 'РД в производство работ',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'РД')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'РД')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'document_name' => 'Комплект приказов ООО «СК ГОРОД»',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Прочее')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Прочее')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Комплект приказов заказчика',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Прочее')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Прочее')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Акт приемки ГРО (акт геодезической разбивки)',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Акт с площадки')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Акт с площадки')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Общий журнал работ',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Журнал')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Журнал верификации (входного контроля)',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Журнал')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Журнал погружения шпунта',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Журнал')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'document_name' => 'Журнал сварочных работ',
                'author_id' => Auth::user()->id,
                'project_object_id' => $objectId,
                'document_type_id' => ProjectObjectDocumentType::where('name', 'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                        ['document_type_id', ProjectObjectDocumentType::where('name', 'Журнал')->first()->id],
                        ['default_selection', 1]
                    ])->first()->document_status_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $sortedTypesIds = ProjectObjectDocumentType::with('projectObjectDocumentStatusTypeRelations')->orderBy('sortOrder')->pluck('id');
        $sortedNewDocuments = [];
        foreach($sortedTypesIds as $typeId)
        {
            $filteredNewDocuments = array_filter($newDocuments, function($elem) use($typeId) {
                return $elem['document_type_id'] === $typeId;
            });

            $sortedNewDocuments = array_merge($sortedNewDocuments, $filteredNewDocuments);
        }

        foreach ($sortedNewDocuments as $newDocument) {
            $id = ProjectObjectDocument::insertGetId($newDocument);

            $actions = new \stdClass;
            $actions->event = 'store';
            $actions->new_values = (object)$newDocument;

            ActionLog::create([
                'logable_id' => $id,
                'logable_type' => 'App\Models\ProjectObjectDocuments\ProjectObjectDocument',
                'actions' => $actions,
                'user_id' => Auth::user()->id,
            ]);
        }
        // ProjectObjectDocument::insert($sortedNewDocuments);
    }

    public function getPermissions() 
    {
        $permissions = (new ProjectObject())->permissions;
        return response()->json($permissions, 200); 
    }
}
