<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Contractors\Contractor;
use App\Models\Permission;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use App\Models\TechAcc\TechnicMovement;
use App\Models\TechAcc\TechnicMovementStatus;
use App\Models\User;
use App\Notifications\Technic\TechnicMovementNotifications;
use App\Services\Common\FilesUploadService;
use App\Services\WorkFlowSupport\TechnicMovementsDispatcher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicMovementController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Перемещения техники';
        $this->baseModel = new TechnicMovement();
        $this->routeNameFixedPart = 'building::tech_acc::technic::movements::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicMovements';
        $this->storage_name = 'technic_movements';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents();
        $this->modulePermissionsGroups = [13];
        $this->ignoreDataKeys[] = 'finish_result';
        $this->ignoreDataKeys[] = 'object';
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        $user = User::find(Auth::user()->id);

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            // ->when(!$user->hasPermission('technics_movement_create_update') && !$user->hasPermission('technics_movement_read'), function($query) use($user) {
            //     return $query->where('responsible_id', $user->id);
            // })
            ->when($user->hasPermission('technics_processing_movement_standart_sized_equipment') && ! $user->is_su, function ($query) {
                return $query->where('technic_category_id', '<>', TechnicCategory::where('name', 'Гусеничные краны')->first()->id);
            })
            ->when($user->hasPermission('technics_processing_movement_oversized_equipment') && ! $user->is_su, function ($query) {
                return $query->where('technic_category_id', TechnicCategory::where('name', 'Гусеничные краны')->first()->id);
            })
            ->when($this->isMobile, function ($query) {
                return $query
                    ->whereNotIn(
                        'technic_movement_status_id',
                        TechnicMovementStatus::whereIn('slug', ['completed', 'cancelled'])->pluck('id')->toArray()
                    )
                    ->with('object');
            })
            ->get();

        return json_encode([
            'data' => $entities,
        ],
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function beforeStore($data)
    {
        // $this->notifyNewResponsible($data, new \stdClass);
        $data['technic_movement_status_id'] = $this->getMovementStatusId($data, new \stdClass);

        return [
            'data' => $data,
        ];
    }

    public function afterStore($entity, $data, $dataToStore)
    {
        if (! empty($data['newAttachments'])) {
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);
        }

        if (! empty($data['deletedAttachments'])) {
            $this->deleteFiles($data['deletedAttachments']);
        }

        new TechnicMovementsDispatcher($data, $entity);
    }

    public function beforeUpdate($entity, $data)
    {
        if (! empty($data['responsible_id'])) {
            if ($entity->responsible_id != $data['responsible_id']) {
                $data['previous_responsible_id'] = $entity->responsible_id;
                // $this->notifyNewResponsible($data, $entity);
            }
        }

        if (! empty($data['movement_start_datetime'])) {
            $data['movement_start_datetime'] = Carbon::parse($data['movement_start_datetime'])->setTimezone('Europe/Moscow');
        }

        $data['technic_movement_status_id'] = $this->getMovementStatusId($data, $entity);

        new TechnicMovementsDispatcher($data, $entity);

        return [
            'data' => $data,
        ];
    }

    public function getMovementStatusId($newData, $dbData)
    {
        $dataObj = $this->getDataObj($newData, $dbData);

        if (! empty($dataObj->finish_result)) {
            if ($dataObj->finish_result === 'completed') {
                return TechnicMovementStatus::where('slug', 'completed')->firstOrFail()->id;
            }
            if ($dataObj->finish_result === 'cancelled') {
                return TechnicMovementStatus::where('slug', 'cancelled')->firstOrFail()->id;
            }
        }

        if (! empty($dataObj->movement_start_datetime) && ! empty($dataObj->responsible_id)) {
            return TechnicMovementStatus::where('slug', 'transportationPlanned')->firstOrFail()->id;
        }

        return TechnicMovementStatus::where('slug', 'created')->firstOrFail()->id;
    }

    // public function notifyNewResponsible($newData, $dbData)
    // {
    //     $dataObj = $this->getDataObj($newData, $dbData);
    //     (new TechnicMovementNotifications)->notifyNewTechnicMovementResponsibleUser($dataObj);
    // }

    public function getDataObj($newData, $dbData)
    {
        $dataObj = new \stdClass;
        $dataObj->technic_category_id = $newData['technic_category_id'] ?? $dbData->technic_category_id ?? null;
        $dataObj->technic_id = $newData['technic_id'] ?? $dbData->technic_id ?? null;
        $dataObj->order_start_date = $newData['order_start_date'] ?? $dbData->order_start_date ?? null;
        $dataObj->order_end_date = $newData['order_end_date'] ?? $dbData->order_end_date ?? null;
        $dataObj->responsible_id = $newData['responsible_id'] ?? $dbData->responsible_id ?? null;
        $dataObj->previous_responsible_id = $newData['previous_responsible_id'] ?? $dbData->previous_responsible_id ?? null;
        $dataObj->object_id = $newData['object_id'] ?? $dbData->object_id ?? null;
        $dataObj->previous_object_id = $newData['previous_object_id'] ?? $dbData->previous_object_id ?? null;
        $dataObj->order_comment = $newData['order_comment'] ?? $dbData->order_comment ?? null;
        $dataObj->finish_result = $newData['finish_result'] ?? $dbData->finish_result ?? null;
        $dataObj->movement_start_datetime = $newData['movement_start_datetime'] ?? $dbData->movement_start_datetime ?? null;
        $dataObj->contractor_id = $newData['contractor_id'] ?? $dbData->contractor_id ?? null;

        return $dataObj;
    }

    public function setAdditionalResources()
    {
        $this->additionalResources->technicCategories = TechnicCategory::all();
        $this->additionalResources->technicMovementStatuses = TechnicMovementStatus::all();
        $this->additionalResources->technicsList = OurTechnic::all();
        $this->additionalResources->technicResponsiblesByTypes =
            [
                'oversize' => User::whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_oversized_equipment'))->get(),
                'standartSize' => User::whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_standart_sized_equipment'))->get(),
            ];
        $this->additionalResources->technicResponsiblesAllTypes =
            User::query()
                ->whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_oversized_equipment'))
                ->orWhereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_standart_sized_equipment'))
                ->get();
        $this->additionalResources->technicCategoryNameAttrs = TechnicMovementNotifications::nameAttrs;
        $this->additionalResources->technicCarriers = Contractor::byTypeSlug('technic_carrier');
        $this->additionalResources->projectObjects =
            ProjectObject::query()
                ->where('is_participates_in_material_accounting', 1)
                ->whereNotNull('short_name')
                ->orderBy('short_name')
                ->get();
    }
}
