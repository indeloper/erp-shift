<?php

namespace App\Http\Controllers\Commerce;

use App\Domain\DTO\ShortNameProjectObject\ShortNameProjectObjectData;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectObjectDocuments\ProjectObjectDocumentsController;
use App\Http\Resources\ProjectObjectResource;
use App\Models\ActionLog;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\ProjectObjectDocuments\ProjectObjectDocument;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatusTypeRelation;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentType;
use App\Models\q3wMaterial\q3wProjectObjectMaterialAccountingType;
use App\Models\User;
use App\Notifications\DocumentFlow\DocumentFlowOnObjectsParticipatesInDocumentFlowNotice;
use App\Notifications\Object\ObjectParticipatesInWorkProductionNotice;
use App\Notifications\Object\ProjectLeaderAppointedToObjectNotice;
use App\Notifications\Object\ResponsibleAddedToObjectNotice;
use App\Services\Bitrix\BitrixServiceInterface;
use App\Services\ShortNameProjectObjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class ObjectController extends Controller
{

    private $components;

    public function __construct($components = [])
    {
        $this->components = $components;
    }

    public function returnPageCore(): View
    {
        //        $basePath = resource_path().'/views/objects';
        //        $componentsPath = resource_path().'/views/objects/desktop/components';
        //        $components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($componentsPath, $basePath);

        return view('objects.desktop.index');
    }

    public function index(Request $request)
    {
        //        $options = json_decode($request['data']);
        //        // unset($options->take);
        //
        //        $objects = (new ProjectObject())
        //            ->dxLoadOptions($options)
        //            ->orderBy('id', 'desc')
        //            ->get();

        $objects = ProjectObject::query()
            ->latest()
            ->paginate(15);

        return ProjectObjectResource::collection($objects);
    }

    public function getMaterialAccountingTypes()
    {
        return response()->json([
            'data' => q3wProjectObjectMaterialAccountingType::all(),
        ]);
    }

    public function getMaterialAccountingTypesItem($id)
    {
        return response()->json([
            'data' => q3wProjectObjectMaterialAccountingType::query()
                ->find($id),
        ]);
    }

    public function getObjectInfoByID(Request $request)
    {
        $allAvailableResponsibles = [
            'pto'      => User::query()->active()
                ->whereIn('group_id', Group::PTO)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get(),
            'managers' => User::query()->active()
                ->whereIn('group_id', Group::PROJECT_MANAGERS)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get(),
            'foremen'  => User::query()->active()
                ->whereIn('group_id', Group::FOREMEN)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get(),
        ];

        $objectAllResponsibles     = ObjectResponsibleUser::where('object_id',
            $request->id);
        $objectResponsibleManagers = clone $objectAllResponsibles;
        $objectResponsiblePTO      = clone $objectAllResponsibles;
        $objectResponsibleForemen  = clone $objectAllResponsibles;

        $objectResponsibleManagers = $objectResponsibleManagers
            ->where(
                'object_responsible_user_role_id',
                (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'))
            ->pluck('user_id')
            ->toArray();

        $objectResponsiblePTO = $objectResponsiblePTO
            ->where(
                'object_responsible_user_role_id',
                (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_PTO_ENGINEER'))
            ->pluck('user_id')
            ->toArray();

        $objectResponsibleForemen = $objectResponsibleForemen
            ->where(
                'object_responsible_user_role_id',
                (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_FOREMAN'))
            ->pluck('user_id')
            ->toArray();

        $objectResponsibles = [
            'pto'      => $objectResponsiblePTO,
            'managers' => $objectResponsibleManagers,
            'foremen'  => $objectResponsibleForemen,
        ];

        $contractors = Contractor::whereIn(
            'id',
            Project::where('object_id', $request->id)->pluck('contractor_id')
                ->toArray()
        )
            ->select('id', 'short_name')
            ->get();

        return json_encode([
            'contractors'              => $contractors,
            'allAvailableResponsibles' => $allAvailableResponsibles,
            'objectResponsibles'       => $objectResponsibles,
        ],
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->input('data'));
        if (empty($request->input('data'))) {
            $data = $request;
        }

        $toUpdateArr = $this->getDataToUpdate($data);

        $object = ProjectObject::create($toUpdateArr);

        $this->syncResponsibles($data, $object->id);

        if ($data->is_participates_in_material_accounting) {
            $this->handleCheckedParticipatesInMaterialAccounting($object->id);
        }

        if ( ! isset($data->is_participates_in_documents_flow)
            and $data->is_participates_in_documents_flow
        ) {
            $this->handleCheckedParticipatesInDocumentsFlow($object->id);
            $this->notifyResponsibleUsers($object->id);
        }

        $this->notifyNewResponsibleUser($object->id,
            $lastObjectResponsibleId = 0);

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'object' => $object,
        ], 200);
    }

    public function getDataToUpdate($data)
    {
        $toUpdateArr = [];

        if (isset($data->bitrix_id)) {
            $toUpdateArr['bitrix_id'] = $data->bitrix_id ? $data->bitrix_id
                : null;
        }
        if (isset($data->name)) {
            $toUpdateArr['name'] = $data->name;
        }
        if (isset($data->address)) {
            $toUpdateArr['address'] = $data->address;
        }
        if (isset($data->cadastral_number)) {
            $toUpdateArr['cadastral_number'] = $data->cadastral_number;
        }
        if (isset($data->short_name)) {
            $toUpdateArr['short_name'] = $data->short_name;
        }

        if (isset($data->material_accounting_type)) {
            $toUpdateArr['material_accounting_type']
                = $data->material_accounting_type;
        }

        if (isset($data->is_participates_in_material_accounting)) {
            $toUpdateArr['is_participates_in_material_accounting']
                = $data->is_participates_in_material_accounting;
        }

        if (isset($data->is_participates_in_documents_flow)) {
            $toUpdateArr['is_participates_in_documents_flow']
                = $data->is_participates_in_documents_flow;
        }

        if (isset($data->is_active)) {
            $toUpdateArr['is_active'] = $data->is_active;
        }

        return $toUpdateArr;
    }

    public function syncResponsibles($request, $id)
    {
        $rolesArray = [
            'responsibles_managers' => (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_PROJECT_MANAGER'),
            'responsibles_pto'      => (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_PTO_ENGINEER'),
            'responsibles_foremen'  => (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_FOREMAN'),
        ];

        $newResponsiblesIds = [];

        foreach ($rolesArray as $roleKey => $roleValue) {
            if (empty($request[$roleKey])) {
                continue;
            }

            if ($request[$roleKey]) {
                foreach ($request[$roleKey] as $user_id) {
                    if ($roleKey === 'responsibles_managers') {
                        if ( ! ObjectResponsibleUser::where([
                            'object_id'                       => $id,
                            'user_id'                         => $user_id,
                            'object_responsible_user_role_id' => $roleValue,
                        ])->first()
                        ) {
                            $this->notifyAboutNewObjectProjectManager(
                                $objectId = $id, $projectManagerId = $user_id);
                        }
                    }

                    $newResponsible       = ObjectResponsibleUser::firstOrCreate([
                        'object_id'                       => $id,
                        'user_id'                         => $user_id,
                        'object_responsible_user_role_id' => $roleValue,
                    ]);
                    $newResponsiblesIds[] = $newResponsible->user_id;
                }
            }
        }

        ObjectResponsibleUser::where('object_id', $id)
            ->whereNotIn('user_id', $newResponsiblesIds)
            ->delete();
    }

    public function handleCheckedParticipatesInMaterialAccounting($objectId)
    {
        $notificationRecipients = ObjectResponsibleUser::query()
            ->where('object_id', $objectId)
            ->where(
                'object_responsible_user_role_id',
                (new ObjectResponsibleUserRole())->getRoleIdBySlug('TONGUE_PTO_ENGINEER'))
            ->pluck('user_id')->toArray();

        $objectName = ProjectObject::findOrFail($objectId)->short_name;

        ObjectParticipatesInWorkProductionNotice::send(
            $notificationRecipients,
            [
                'name' => 'Объект:'."\n".$objectName."\n"
                    .'участвует в производстве работ.',
            ]
        );
    }

    public function handleCheckedParticipatesInDocumentsFlow($objectId)
    {
        if (ProjectObjectDocument::where([
            ['project_object_id', $objectId],
            [
                'document_status_id',
                ProjectObjectDocumentStatus::where('name', 'В архиве')
                    ->first()->id,
            ],
        ])->exists()
        ) {
            $this->returnDocumentsFromArchive($objectId);
        } else {
            $this->createProjectObjectDocuments($objectId);
        }
    }

    public function returnDocumentsFromArchive($objectId)
    {
        $archivedObjectDocumentsIds = ProjectObjectDocument::where([
            ['project_object_id', $objectId],
            [
                'document_status_id',
                ProjectObjectDocumentStatus::where('name', 'В архиве')
                    ->first()->id,
            ],
        ])->pluck('id');

        foreach ($archivedObjectDocumentsIds as $id) {
            (new ProjectObjectDocumentsController())->restoreDocument($id);
        }
    }

    public function createProjectObjectDocuments($objectId)
    {
        $newDocuments = [
            [
                'document_name'      => 'ППР',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'ППР')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'ППР')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Акт-допуск на площадку',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Акт с площадки')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name',
                            'Акт с площадки')->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Акт передачи фронта работ',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Акт с площадки')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name',
                            'Акт с площадки')->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],

            [
                'document_name'      => 'РД в производство работ',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'РД')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'РД')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],

            [
                'document_name'      => 'Комплект приказов ООО «СК ГОРОД»',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Прочее')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'Прочее')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Комплект приказов заказчика',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Прочее')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'Прочее')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Акт приемки ГРО (акт геодезической разбивки)',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Акт с площадки')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name',
                            'Акт с площадки')->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Общий журнал работ',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'Журнал')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Журнал верификации (входного контроля)',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'Журнал')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Журнал погружения шпунта',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'Журнал')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'document_name'      => 'Журнал сварочных работ',
                'author_id'          => Auth::user()->id,
                'project_object_id'  => $objectId,
                'document_type_id'   => ProjectObjectDocumentType::where('name',
                    'Журнал')->first()->id,
                'document_status_id' => ProjectObjectDocumentStatusTypeRelation::where([
                    [
                        'document_type_id',
                        ProjectObjectDocumentType::where('name', 'Журнал')
                            ->first()->id,
                    ],
                    ['default_selection', 1],
                ])->first()->document_status_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ];

        $sortedTypesIds
                            = ProjectObjectDocumentType::with('projectObjectDocumentStatusTypeRelations')
            ->orderBy('sortOrder')->pluck('id');
        $sortedNewDocuments = [];
        foreach ($sortedTypesIds as $typeId) {
            $filteredNewDocuments = array_filter($newDocuments,
                function ($elem) use ($typeId) {
                    return $elem['document_type_id'] === $typeId;
                });

            $sortedNewDocuments = array_merge($sortedNewDocuments,
                $filteredNewDocuments);
        }

        foreach ($sortedNewDocuments as $newDocument) {
            $id = ProjectObjectDocument::insertGetId($newDocument);

            (new ProjectObjectDocumentsController(['Документ создан']))->addComment($id);

            $actions             = new stdClass();
            $actions->event      = 'store';
            $actions->new_values = (object) $newDocument;

            ActionLog::create([
                'logable_id'   => $id,
                'logable_type' => \App\Models\ProjectObjectDocuments\ProjectObjectDocument::class,
                'actions'      => $actions,
                'user_id'      => Auth::user()->id,
            ]);
        }
        // ProjectObjectDocument::insert($sortedNewDocuments);
    }

    public function notifyResponsibleUsers($objectId)
    {
        if ( ! App::environment() != 'production') {
            return;
        }

        $notificationRecipients = ObjectResponsibleUser::query()
            ->where('object_id', $objectId)
            ->where('user_id', '<>', Auth::user()->id)
            ->pluck('user_id');

        $objectName = ProjectObject::findOrFail($objectId)->short_name;

        DocumentFlowOnObjectsParticipatesInDocumentFlowNotice::send(
            $notificationRecipients,
            [
                'name' => 'Документооборот на объектах'."\n".$objectName."\n"
                    .'Участвует в документообороте',
            ]
        );
    }

    public function notifyNewResponsibleUser(
        $objectId,
        $lastObjectResponsibleId
    ) {
        $notificationRecipients = ObjectResponsibleUser::where([
            ['id', '>', $lastObjectResponsibleId],
            ['object_id', $objectId],
            ['user_id', '<>', Auth::user()->id],
        ])->pluck('user_id');

        $objectName = ProjectObject::findOrFail($objectId)->short_name;

        if ($notificationRecipients->count()) {
            ResponsibleAddedToObjectNotice::send(
                $notificationRecipients,
                [
                    'name' => 'Вы добавлены ответственным на объект'."\n"
                        .$objectName,
                ]
            );
        }
    }

    public function notifyAboutNewObjectProjectManager(
        $objectId,
        $projectManagerId
    ) {
        $notificationRecipients
                            = (new Permission())->getUsersIdsByCodename('notify_about_new_object_project_manager');
        $objectName         = ProjectObject::findOrFail($objectId)->short_name;
        $projectManagerName = User::findOrFail($projectManagerId)->full_name;

        ProjectLeaderAppointedToObjectNotice::send(
            $notificationRecipients,
            [
                'name' => 'На объект'."\n".$objectName."\n"
                    .'назначен руководитель проекта '."\n".$projectManagerName,
            ]
        );
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->get('data');

        $toUpdateArr = $request->get('data');

        DB::beginTransaction();

        /** @var ProjectObject $object */
        $object = ProjectObject::findOrFail($id);
        $oldIsParticipatesInDocumentsFlow
                = $object->is_participates_in_documents_flow;
        $oldIsParticipatesInMaterialAccounting
                = $object->is_participates_in_material_accounting;

        $lastObjectResponsibleId = ObjectResponsibleUser::orderByDesc('id')
            ->first()->id;

        $toUpdateArr['is_participates_in_material_accounting']
            = $toUpdateArr['is_participates_in_material_accounting'] === 'true';
        $toUpdateArr['is_participates_in_documents_flow']
            = $toUpdateArr['is_participates_in_documents_flow'] === 'true';

        $object->fill($toUpdateArr);

        $firstLatter = str($object->direction->name())->substr(0,
            1);

        $object->update([
            'short_name' =>
                $firstLatter->upper()->toString()
                .', '.str($object->short_name)->replace(['Ш,', 'С,'],
                    '')
                    ->toString(),
        ]);

        $object->save();

        if (isset($data['short_name_detail'])) {
            app(ShortNameProjectObjectService::class)
                ->store(
                    \auth()->user(),
                    $object,
                    ShortNameProjectObjectData::make(
                        json_decode(
                            $data['short_name_detail'],
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        )
                    )
                );
        }

        $this->syncResponsibles($data, $id);

        if (isset($data['is_participates_in_material_accounting'])) {
            if ($data['is_participates_in_material_accounting']
                > $oldIsParticipatesInMaterialAccounting
            ) {
                $this->handleCheckedParticipatesInMaterialAccounting($id);
            }
        }

        if (isset($data['is_participates_in_documents_flow'])) {
            if ($data['is_participates_in_documents_flow']
                > $oldIsParticipatesInDocumentsFlow
            ) {
                $this->handleCheckedParticipatesInDocumentsFlow($id);
                $this->notifyResponsibleUsers($id);
            } elseif ($data['is_participates_in_documents_flow']
                < $oldIsParticipatesInDocumentsFlow
            ) {
                $this->addDocumentsToArchive($id);
            }
        }

        $this->notifyNewResponsibleUser($id, $lastObjectResponsibleId);

        if (isset($object->bitrix_id)) {
            \app(BitrixServiceInterface::class)->updateDealByModal(
                $object
            );
        }

        DB::commit();

        return response()->json([
            'result'  => 'ok',
            'object'  => $object,
            'updated' => $toUpdateArr,
        ], 200);
    }

    public function addDocumentsToArchive($objectId)
    {
        $objectDocumentsIds = ProjectObjectDocument::where(
            'project_object_id', $objectId
        )->pluck('id');

        foreach ($objectDocumentsIds as $id) {
            $archivedStatusId = ProjectObjectDocumentStatus::where('name',
                'В архиве')->first()->id;
            ProjectObjectDocument::find($id)->update([
                'document_status_id' => $archivedStatusId,
            ]);

            (new ProjectObjectDocumentsController(['Документ перемещен в архив']))->addComment($id);
            (new ProjectObjectDocumentsController())->addDataToActionLog('archive',
                ['document_status_id' => $archivedStatusId], $id);
        }
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
                $objects->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('address', 'like', '%'.$request->q.'%')
                    ->orWhere('short_name', 'like', '%'.$request->q.'%');
            });
        }
        $objects = $objects->take(10)->get();

        return $objects->map(function ($object) {
            return ['code' => $object->id.'', 'label' => $object->address];
        });
    }

    public function getPermissions(): JsonResponse
    {
        $permissions = (new ProjectObject())->permissions;

        return response()->json($permissions, 200);
    }

}
