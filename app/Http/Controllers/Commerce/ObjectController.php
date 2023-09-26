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
use App\Services\SystemService;
use App\Http\Requests\ObjectRequests\ObjectRequest;
use App\Models\ActionLog;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\Notification;
use App\Models\ProjectObjectDocuments\ProjectObjectDocument;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatusTypeRelation;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentType;
use App\Models\q3wMaterial\q3wProjectObjectMaterialAccountingType;
use App\Models\User;
use App\Services\Common\FileSystemService;
use Illuminate\Support\Facades\App;

class ObjectController extends Controller
{
    private $components;

    public function __construct ($components = [])
    {
        $this->components = $components;
    }

    public function returnPageCore()
    {
        $basePath = resource_path().'/views/objects';
        $componentsPath = resource_path().'/views/objects/desktop/components';
        $components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($componentsPath, $basePath);
        return view('objects.desktop.index', compact('components'));
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);
        // unset($options->take);

        $objects = (new ProjectObject)
            ->dxLoadOptions($options)
            ->orderBy('id', 'desc')
            ->get();

        return json_encode(array(
            "data" => $objects
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }



    public function getMaterialAccountingTypes()
    {
        return q3wProjectObjectMaterialAccountingType::all();
    }

    public function getObjectInfoByID(Request $request)
    {
        $allAvailableResponsibles = [
            'pto' => User::query()->active()->whereIn('group_id', Group::PTO)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get(),
            'managers' => User::query()->active()->whereIn('group_id', Group::PROJECT_MANAGERS)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get(),
            'foremen' => User::query()->active()->whereIn('group_id', Group::FOREMEN)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get(),
        ];

        $objectAllResponsibles = ObjectResponsibleUser::where('object_id', $request->id);
        $objectResponsibleManagers = clone $objectAllResponsibles;
        $objectResponsiblePTO = clone $objectAllResponsibles;
        $objectResponsibleForemen = clone $objectAllResponsibles;

        $objectResponsibleManagers =
            $objectResponsibleManagers
                ->where(
                    'object_responsible_user_role_id',
                    (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'))
                ->pluck('user_id')
                ->toArray();

        $objectResponsiblePTO =
            $objectResponsiblePTO
                ->where(
                    'object_responsible_user_role_id',
                    (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PTO_ENGINEER'))
                ->pluck('user_id')
                ->toArray();

        $objectResponsibleForemen =
            $objectResponsibleForemen
                ->where(
                    'object_responsible_user_role_id',
                    (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_FOREMAN'))
                ->pluck('user_id')
                ->toArray();

        $objectResponsibles = [
            'pto' => $objectResponsiblePTO,
            'managers' => $objectResponsibleManagers,
            'foremen' => $objectResponsibleForemen,
        ];

        $contractors = Contractor::whereIn(
                'id',
                Project::where('object_id', $request->id)->pluck('id')->toArray()
            )
            ->select('id', 'short_name')
            ->get();


        return json_encode(array(
            'contractors' => $contractors,
            'allAvailableResponsibles' => $allAvailableResponsibles,
            'objectResponsibles' => $objectResponsibles
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function store(Request $request)
    {
        $data = json_decode($request->input('data'));
        if(empty($request->input('data')))
        $data = $request;

        $toUpdateArr = $this->getDataToUpdate($data);

        $object = ProjectObject::create($toUpdateArr);

        $this->syncResponsibles($data, $object->id);

        if(!isset($data->is_participates_in_documents_flow)){
            $this->handleCheckedParticipatesInDocumentsFlow($object->id);
            $this->notifyResponsibleUsers($object->id);
        }

        $this->notifyNewResponsibleUser($object->id, $lastObjectResponsibleId = 0);

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'object' => $object,
        ], 200);


    }

    public function update(Request $request, $id)
    {
        $data = json_decode($request->input('data'));
        $toUpdateArr = $this->getDataToUpdate($data);

        DB::beginTransaction();

        $object = ProjectObject::findOrFail($id);

        $checkedParticipatesInDocumentsFlow = false;
        $uncheckedParticipatesInDocumentsFlow = false;

        $lastObjectResponsibleId = ObjectResponsibleUser::orderByDesc('id')->first()->id;

        if(isset($data->is_participates_in_documents_flow) && !$object->is_participates_in_documents_flow)
        $checkedParticipatesInDocumentsFlow = true;

        if(!isset($data->is_participates_in_documents_flow) && $object->is_participates_in_documents_flow)
        $uncheckedParticipatesInDocumentsFlow = true;

        $object->update($toUpdateArr);

        $this->syncResponsibles($data, $id);

        if($checkedParticipatesInDocumentsFlow){
            $this->handleCheckedParticipatesInDocumentsFlow($id);
            $this->notifyResponsibleUsers($id);
        }

        if($uncheckedParticipatesInDocumentsFlow)
            $this->addDocumentsToArchive($id);

        $this->notifyNewResponsibleUser($id, $lastObjectResponsibleId);

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'object' => $object,
            'updated' => $toUpdateArr
        ], 200);
    }

    public function getDataToUpdate($data)
    {
        $toUpdateArr = [];

        if(!empty($data->bitrixId))
        $toUpdateArr['bitrixId'] = $data->bitrixId;
        if(!empty($data->name))
        $toUpdateArr['name'] = $data->name;
        if(!empty($data->address))
        $toUpdateArr['address'] = $data->address;
        if(!empty($data->cadastral_number))
        $toUpdateArr['cadastral_number'] = $data->cadastral_number;
        if(!empty($data->short_name))
        $toUpdateArr['short_name'] = $data->short_name;

        if(!empty($data->material_accounting_type))
        $toUpdateArr['material_accounting_type'] = $data->material_accounting_type;

        if(!empty($data->is_participates_in_material_accounting))
            $toUpdateArr['is_participates_in_material_accounting'] =
                $data->is_participates_in_material_accounting ? 1 : 0;
        else
            $toUpdateArr['is_participates_in_material_accounting'] = 0;

        if(!empty($data->is_participates_in_documents_flow))
            $toUpdateArr['is_participates_in_documents_flow'] =
                $data->is_participates_in_documents_flow ? 1 : 0;
        else
            $toUpdateArr['is_participates_in_documents_flow'] = 0;

        if(!empty($data->is_active))
        $toUpdateArr['is_active'] = $data->is_active;

        return $toUpdateArr;
    }


    public function syncResponsibles($request, $id)
    {
        // if(Auth::user()->isProjectManager() or Auth::user()->isInGroup(43)/*8*/)
        // return;

        $rolesArray = [
            'responsibles_managers' => (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'),
            'responsibles_pto' => (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PTO_ENGINEER'),
            'responsibles_foremen' => (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_FOREMAN'),
        ];

        $newResponsiblesIds = [];

        foreach($rolesArray as $roleKey=>$roleValue){

            if(empty($request->$roleKey))
            continue;

            if ($request->$roleKey) {
                foreach ($request->$roleKey as $user_id) {
                    $newResponsible = ObjectResponsibleUser::firstOrCreate([
                        'object_id' => $id,
                        'user_id' => $user_id,
                        'object_responsible_user_role_id' => $roleValue
                    ]);
                    $newResponsiblesIds[] = $newResponsible->user_id;
                }
            }
        }

        ObjectResponsibleUser::where('object_id', $id)
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
