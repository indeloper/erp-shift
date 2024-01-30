<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Company\Company;
use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use App\Services\Common\FilesUploadService;
use App\Services\Fuel\FuelLevelSyncOnFlowCreatedService;
use App\Services\Fuel\FuelLevelSyncOnFlowDeletedService;
use App\Services\Fuel\FuelLevelSyncOnFlowUpdatedService;
use App\Services\Fuel\FuelLevelUpdateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuelTankFlowController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->baseModel = new FuelTankFlow();
        $this->routeNameFixedPart = 'building::tech_acc::fuel::fuelFlow::';
        $this->sectionTitle = 'Топливный журнал';
        $this->baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/flow';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->storage_name = 'fuel_flow';
        $this->components = $this->getModuleComponents();
        $this->modulePermissionsGroups = [17];
        $this->ignoreDataKeys[] = 'third_party_mark';
        $this->ignoreDataKeys[] = 'fuelConsumerType';
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        if(!empty($options->group)) {
                $groups = $this->handleCustomGroupResponse($options);
                return json_encode(array(
                        'data' => $groups
                    ),
                    JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }

        if(!empty($options->sort)) {
            $options->sort[0]->selector = 'event_date';
        }

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->leftJoin('our_technics', 'our_technics.id', '=', 'fuel_tank_flows.our_technic_id')
            ->select(['fuel_tank_flows.*', 'our_technics.third_party_mark'])
            // ->when(!User::find(Auth::user()->id)->hasPermission('adjust_fuel_tank_remains'), function($query) {
            //     return $query->where('fuel_tank_flow_type_id', '<>', FuelTankFlowType::where('slug', 'adjustment')->first()->id);
            // })
            ->when(!User::find(Auth::user()->id)->hasPermission('watch_any_fuel_tank_flows'), function($query) {
                return $query->where('fuel_tank_flows.responsible_id', Auth::user()->id);
            })
            ->get();


        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function handleCustomGroupResponse($options)
    {
        $entities = (new FuelTankFlow)
            ->dxLoadOptions($options)
            ->when(!User::find(Auth::user()->id)->hasPermission('adjust_fuel_tank_remains'), function($query) {
                return $query->where('fuel_tank_flow_type_id', '<>', FuelTankFlowType::where('slug', 'adjustment')->first()->id);
            })
            ->when(!User::find(Auth::user()->id)->hasPermission('watch_any_fuel_tank_flows'), function($query) {
                return $query->where('responsible_id', Auth::user()->id);
            })
            ;

        $groupsData =
            $entities
            ->select(DB::raw('EXTRACT( YEAR_MONTH FROM `event_date`) as y_m'))
            ->addSelect(DB::raw('EXTRACT( YEAR FROM `event_date`) as y'))
            ->addSelect(DB::raw('EXTRACT( MONTH FROM `event_date`) as m'))
            ->selectRaw('count(`event_date`) as qty')
            ->groupBy('y_m')
            ->orderByDesc('y_m')
            ->get();

        $groups = [];
        foreach($groupsData as $groupArr) {
            $groups[] = [
                'count' => $groupArr->qty,
                'key' => 'event_date',
                'items' => null,
                'summary' => [
                    'year' => $groupArr->y,
                    'month' => $groupArr->m
                ]
            ];
        }
        return $groups;
    }

    public function beforeStore($data)
    {
        $tank = FuelTank::findOrFail($data['fuel_tank_id']);

        $data['author_id'] = Auth::id();

        [$data['responsible_id'], $data['object_id']] = $this->getFuelFlowResponsibleAndObject($tank);

        $data['company_id'] = $tank->company_id;

        return [
            'data' => $data,
        ];
    }

    public function afterStore($entity, $data, $dataToStore)
    {
        if(!empty($data['newAttachments']))
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);

        if(!empty($data['deletedAttachments']))
            $this->deleteFiles($data['deletedAttachments']);

        $lastFuelTankTransferHistory = FuelTankTransferHistory::where('fuel_tank_id', $data['fuel_tank_id'])->orderBy('id', 'desc')->first();
        if(!$lastFuelTankTransferHistory) {
            $lastFuelTankTransferHistory = new FuelTankTransferHistory;
        }

        $tank = FuelTank::find($entity->fuel_tank_id);

        $newFuelTankTransferHistory = FuelTankTransferHistory::create([
            'author_id' => Auth::id(),
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $lastFuelTankTransferHistory->previous_object_id ?? null,
            'object_id' => $entity->object_id,
            'previous_responsible_id' => $lastFuelTankTransferHistory->previous_responsible_id ?? null,
            'responsible_id' => $entity->responsible_id,
            'fuel_tank_flow_id' => $entity->id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => $entity->event_date
        ]);

        new FuelLevelSyncOnFlowCreatedService($newFuelTankTransferHistory);
    }

    public function beforeUpdate($entity, $data)
    {
        // if ($entity->volume != $data['volume'] || $entity->fuel_tank_id != $data['fuel_tank_id']) {
        //     $fuelLevel = $this->syncFuelLevelData($entity, $data);
        // }

        $historyLog = FuelTankTransferHistory::where(['fuel_tank_flow_id' => $entity->id])->orderByDesc('id')->first();

        if ($entity->volume != $data['volume'] || $entity->fuel_tank_id != $data['fuel_tank_id']) {
            new FuelLevelSyncOnFlowUpdatedService($historyLog, $data);
        }

        $historyLog->fuel_tank_id = $data['fuel_tank_id'];
        $historyLog->event_date = $data['event_date'];

        if(isset($fuelLevel)) {
            $historyLog->fuel_level = $fuelLevel;
        }

        $historyLog->save();

        $data['our_technic_id'] = $data['our_technic_id'] ?? null;
        $data['third_party_consumer'] = $data['third_party_consumer'] ?? null;

        return [
            'data' => $data,
        ];
    }

    public function beforeDelete($entity)
    {
        $fuelflowHistory = FuelTankTransferHistory::where('fuel_tank_flow_id', $entity->id)->orderByDesc('id')->first();
        if($fuelflowHistory) {
            new FuelLevelSyncOnFlowDeletedService($fuelflowHistory);
            $fuelflowHistory->delete();
        }
    }

    public function getFuelFlowResponsibleAndObject($tank)
    {
        if(!$tank->awaiting_confirmation) {
            return [$tank->responsible_id, $tank->object_id];
        }

        $transferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->whereNull('fuel_tank_flow_id')
            ->orderByDesc('id')
            ->first();

        if($transferHistory && $transferHistory->previous_responsible_id) {
            return [$transferHistory->previous_responsible_id, $transferHistory->previous_object_id];
        } else {
            return [$tank->responsible_id, $tank->object_id];
        }
    }

    public function syncFuelLevelData($entity, $data)
    {
        $tank = FuelTank::find($data['fuel_tank_id']);

        if ($entity->fuel_tank_id != $data['fuel_tank_id']) {
            $oldTank = FuelTank::find($entity->fuel_tank_id);

            if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'outcome') {
                $oldTank->fuel_level = round($oldTank->fuel_level + $entity->volume);
                $tank->fuel_level = round($tank->fuel_level - $entity->volume);
            }

            if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'income') {
                $oldTank->fuel_level = round($oldTank->fuel_level - $entity->volume);
                $tank->fuel_level = round($tank->fuel_level + $entity->volume);
            }

            if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'adjustment') {
                $oldTank->fuel_level = round($oldTank->fuel_level - $entity->volume);
                $tank->fuel_level = round($tank->fuel_level + $entity->volume);
            }

            $oldTank->save();
            $tank->save();
        }

        if ($entity->volume != $data['volume']) {

            if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'outcome') {
                if ($data['volume'] > $entity->volume) {
                    $tank->fuel_level = $tank->fuel_level - ($data['volume'] - $entity->volume);
                }
                else {
                    $tank->fuel_level = $tank->fuel_level + ($data['volume'] - $entity->volume);
                }
            }

            if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'income') {
                if ($data['volume'] > $entity->volume) {
                    $tank->fuel_level = $tank->fuel_level + ($data['volume'] - $entity->volume);
                }
                else {
                    $tank->fuel_level = $tank->fuel_level - ($data['volume'] - $entity->volume);
                }
            }

            if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'adjustment') {
                $tank->fuel_level - $entity->volume + $data['volume'];
            }

            $tank->save();
        }

        return $tank->fuel_level;
    }

    public function setAdditionalResources()
    {
        $this->additionalResources->
        projectObjects =
            ProjectObject::query()
                ->where('is_participates_in_material_accounting', 1)
                ->whereNotNull('short_name')
                ->get();

        $this->additionalResources->
        companies =
            Company::all();

        $this->additionalResources->
        fuelFlowTypes =
            FuelTankFlowType::all();

        $this->additionalResources->
        fuelTanks =
            FuelTank::all();

        $this->additionalResources->
        fuelResponsibles =
            User::query()->active()
                ->orWhereIn('group_id', Group::FOREMEN)
                ->get();

        $this->additionalResources->
        fuelContractors =
            Contractor::byTypeSlug('fuel_supplier');

        $this->additionalResources->
        fuelConsumers =
            OurTechnic::all();

        $this->additionalResources->
        users =
            User::query()->active()->get();
    }

}
