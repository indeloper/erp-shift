<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

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
use App\Services\Fuel\FuelFlowCrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuelTankFlowController extends StandardEntityResourceController
{

    public function __construct()
    {
        parent::__construct();

        $this->baseModel               = new FuelTankFlow();
        $this->routeNameFixedPart      = 'building::tech_acc::fuel::fuelFlow::';
        $this->sectionTitle            = 'Топливный журнал';
        $this->baseBladePath           = resource_path()
            .'/views/tech_accounting/fuel/tanks/flow';
        $this->componentsPath          = $this->baseBladePath
            .'/desktop/components';
        $this->storage_name            = 'fuel_flow';
        $this->components              = $this->getModuleComponents();
        $this->modulePermissionsGroups = [17];
        $this->ignoreDataKeys[]        = 'third_party_mark';
        $this->ignoreDataKeys[]        = 'fuelConsumerType';
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        if ( ! empty($options->group)) {
            $groups = $this->handleCustomGroupResponse($options);

            return json_encode([
                'data' => $groups,
            ],
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }

        if ( ! empty($options->sort)) {
            $options->sort[0]->selector = 'event_date';
        }

        // TODO Временная заглушка. До лучшего рефакторинга
        foreach ($options->filter as $key => $sort) {
            if ($sort[0] === 'responsible_id') {
                $options->filter[$key][0] = 'fuel_tank_flows.responsible_id';
            }

            if (is_array($sort[0])) {
                if ($sort[0][0] === 'responsible_id') {
                    $options->filter[$key][0][0]
                        = 'fuel_tank_flows.responsible_id';
                }
            }
        }

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->leftJoin('our_technics', 'our_technics.id', '=',
                'fuel_tank_flows.our_technic_id')
            ->select(['fuel_tank_flows.*', 'our_technics.third_party_mark'])
            // ->when(!User::find(Auth::user()->id)->hasPermission('adjust_fuel_tank_remains'), function($query) {
            //     return $query->where('fuel_tank_flow_type_id', '<>', FuelTankFlowType::where('slug', 'adjustment')->first()->id);
            // })
            ->when(! User::find(Auth::user()->id)
                ->hasPermission('watch_any_fuel_tank_flows'),
                function ($query) {
                    return $query->where('fuel_tank_flows.responsible_id',
                        Auth::user()->id);
                })
            ->orderBy('event_date')
            ->orderByDesc('id')
            ->get();

        return json_encode([
            'data' => $entities,
        ],
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function handleCustomGroupResponse($options)
    {
        $entities = (new FuelTankFlow())
            ->dxLoadOptions($options)
            // ->when(!User::find(Auth::user()->id)->hasPermission('adjust_fuel_tank_remains'), function($query) {
            //     return $query->where('fuel_tank_flow_type_id', '<>', FuelTankFlowType::where('slug', 'adjustment')->first()->id);
            // })
            ->when(! User::find(Auth::user()->id)
                ->hasPermission('watch_any_fuel_tank_flows'),
                function ($query) {
                    return $query->where('responsible_id', Auth::user()->id);
                });

        $groupsData = $entities
            ->select(DB::raw('EXTRACT( YEAR_MONTH FROM `event_date`) as y_m'))
            ->addSelect(DB::raw('EXTRACT( YEAR FROM `event_date`) as y'))
            ->addSelect(DB::raw('EXTRACT( MONTH FROM `event_date`) as m'))
            ->selectRaw('count(`event_date`) as qty')
            ->groupBy('y_m')
            ->orderByDesc('y_m')
            ->get();

        $groups = [];
        foreach ($groupsData as $groupArr) {
            $groups[] = [
                'count'   => $groupArr->qty,
                'key'     => 'event_date',
                'items'   => null,
                'summary' => [
                    'year'  => $groupArr->y,
                    'month' => $groupArr->m,
                ],
            ];
        }

        return $groups;
    }

    public function beforeStore($data)
    {
        $data['author_id'] = Auth::id();

        if ($data['fuel_tank_flow_type_id'] === FuelTankFlowType::where('slug',
                'simultaneous_income_outcome')->first()->id
        ) {
            $data['responsible_id'] = Auth::id();
        } else {
            $tank = FuelTank::findOrFail($data['fuel_tank_id']);
            [$data['responsible_id'], $data['object_id']]
                = $this->getFuelFlowResponsibleAndObject($tank);
            $data['company_id'] = $tank->company_id;
        }

        return [
            'data' => $data,
        ];
    }

    public function afterStore($entity, $data, $dataToStore)
    {
        if ( ! empty($data['newAttachments'])) {
            (new FilesUploadService())->attachFiles($entity,
                $data['newAttachments']);
        }

        if ( ! empty($data['deletedAttachments'])) {
            $this->deleteFiles($data['deletedAttachments']);
        }

        if ($data['fuel_tank_flow_type_id'] != FuelTankFlowType::where('slug',
                'simultaneous_income_outcome')->first()->id
        ) {
            (new FuelFlowCrudService('stored', [
                'entity'      => $entity,
                'data'        => $data,
                'dataToStore' => $dataToStore,
            ]));
        }
    }

    public function beforeUpdate($entity, $data)
    {
        if ($data['fuel_tank_flow_type_id'] != FuelTankFlowType::where('slug',
                'simultaneous_income_outcome')->first()->id
        ) {
            (new FuelFlowCrudService('updated', [
                'entity' => $entity,
                'data'   => $data,
            ]));
        }

        $data['our_technic_id']       = $data['our_technic_id'] ?? null;
        $data['third_party_consumer'] = $data['third_party_consumer'] ?? null;

        return [
            'data' => $data,
        ];
    }

    public function beforeDelete($entity)
    {
        if ($entity['fuel_tank_flow_type_id'] != FuelTankFlowType::where('slug',
                'simultaneous_income_outcome')->first()->id
        ) {
            (new FuelFlowCrudService('deleted', [
                'entity' => $entity,
            ]));
        }
    }

    public function getFuelFlowResponsibleAndObject($tank)
    {
        if ( ! $tank->awaiting_confirmation) {
            return [$tank->responsible_id, $tank->object_id];
        }

        $transferHistory = FuelTankTransferHistory::query()
            ->where('fuel_tank_id', $tank->id)
            ->whereNull('fuel_tank_flow_id')
            ->orderByDesc('id')
            ->first();

        if ($transferHistory && $transferHistory->previous_responsible_id) {
            return [
                $transferHistory->previous_responsible_id,
                $transferHistory->previous_object_id,
            ];
        } else {
            return [$tank->responsible_id, $tank->object_id];
        }
    }

    public function setAdditionalResources()
    {
        $this->additionalResources->projectObjects = ProjectObject::query()
            // ->where('is_participates_in_material_accounting', 1)
            ->whereNotNull('short_name')
            ->get();

        $this->additionalResources->companies = Company::all();

        $this->additionalResources->fuelFlowTypes = FuelTankFlowType::all();

        $this->additionalResources->fuelTanks
            = FuelTank::leftJoin('fuel_tank_transfer_histories',
            function ($join) {
                $join->on('fuel_tank_transfer_histories.fuel_tank_id', '=',
                    'fuel_tanks.id')
                    ->where('tank_moving_confirmation', true);
            })
            ->selectRaw('
                fuel_tanks.*,
                MAX(event_date) as lastMovementConfirmationDate
            ')
            ->groupBy('fuel_tanks.id')
            ->get();

        $this->additionalResources->fuelResponsibles = User::query()->active()
            ->orWhereIn('group_id', Group::FOREMEN)
            ->get();

        $this->additionalResources->fuelContractors
            = Contractor::byTypeSlug('fuel_supplier');

        $this->additionalResources->fuelConsumers = OurTechnic::all();

        $this->additionalResources->users = User::query()->active()->get();
    }

}
