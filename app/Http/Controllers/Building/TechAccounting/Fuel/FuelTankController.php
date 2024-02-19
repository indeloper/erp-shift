<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use App\Actions\Fuel\FuelActions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Comment;
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
use App\Notifications\Fuel\FuelNotifications;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuelTankController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Топливные емкости';
        $this->baseModel = new FuelTank;
        $this->routeNameFixedPart = 'building::tech_acc::fuel::tanks::';
        $this->baseBladePath = resource_path() . '/views/tech_accounting/fuel/tanks/objects';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents();
        $this->modulePermissionsGroups = [17];
        $this->ignoreDataKeys[] = 'externalOperations';
        $this->ignoreDataKeys[] = 'externalDeletedOperations';
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        $searchValueQuery = '';

        if ($this->isMobile && $options->searchValue) {
            $searchValueQuery = $options->searchValue;
            unset($options->searchValue);
            unset($options->searchOperation);
        }

        $userId = Auth::user()->id;

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->when(!User::find($userId)->hasPermission('watch_any_fuel_tanks'), function ($query) use ($userId) {
                return $query->where('responsible_id', $userId);
            })
            ->selectRaw(
                '
                    fuel_tanks.id,
                    fuel_tanks.explotation_start,
                    fuel_tanks.company_id,
                    fuel_tanks.tank_number,
                    fuel_tanks.object_id,
                    fuel_tanks.responsible_id,
                    fuel_tanks.fuel_level,
                    fuel_tanks.awaiting_confirmation,
                    fuel_tanks.comment_movement_tmp,
                    (SELECT MAX(`event_date`) from `fuel_tank_flows` where `fuel_tank_flows`.`fuel_tank_id` = `fuel_tanks`.`id`)
                    as max_event_date,
                    (SELECT `previous_object_id` from `fuel_tank_transfer_histories` where id = (SELECT MAX(`id`) from `fuel_tank_transfer_histories` where `fuel_tank_transfer_histories`.`fuel_tank_id` = `fuel_tanks`.`id` and `fuel_tank_transfer_histories`.`fuel_tank_flow_id` is null))
                    as previous_object_id,
                    (SELECT `previous_responsible_id` from `fuel_tank_transfer_histories` where id = (SELECT MAX(`id`) from `fuel_tank_transfer_histories` where `fuel_tank_transfer_histories`.`fuel_tank_id` = `fuel_tanks`.`id` and `fuel_tank_transfer_histories`.`fuel_tank_flow_id` is null))
                    as previous_responsible_id
                '
            )
            ->when($this->isMobile, function ($query) {
                return $query->with('object', 'responsible');
            })
            ->when(!empty($searchValueQuery), function ($query) use ($searchValueQuery) {
                $objectIds = ProjectObject::where('short_name', 'LIKE', '%' . $searchValueQuery . '%')->pluck('id')->toArray();
                $tankIds = FuelTank::where('tank_number', 'LIKE', '%' . $searchValueQuery . '%')->pluck('id')->toArray();
                return $query
                    ->whereIn('object_id', $objectIds)
                    ->orWhereIn('id', $tankIds);
            })
            ->get();

        return json_encode(array(
            "data" => $entities
        ),
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

    }

    public function afterStore($tank, $data, $dataToStore)
    {
        if(empty($data['externalOperations'])) {
            FuelTankTransferHistory::create([
                'author_id' => Auth::user()->id,
                'fuel_tank_id' => $tank->id,
                'object_id' => $tank->object_id,
                'responsible_id' => $tank->responsible_id,
                'fuel_level' => 0,
                'event_date' => $data['event_date'] ?? now()
            ]);
        }
        
        if(!empty($data['externalOperations'])) {
            $this->handleFuelOperations($data['externalOperations'], $data['externalDeletedOperations'], $tank->id);
        }
    }

    public function beforeUpdate($tank, $data)
    {
        if(empty($data['externalOperations']) && empty($data['externalDeletedOperations'])) {
            FuelTankTransferHistory::create([
                'author_id' => Auth::user()->id,
                'fuel_tank_id' => $tank->id,
                'previous_object_id' => $tank->object_id,
                'object_id' => $data['object_id'] ?? $tank->object_id ?? null,
                'previous_responsible_id' => $tank->responsible_id,
                'responsible_id' => $data['responsible_id'] ?? $tank->responsible_id ?? null,
                'fuel_level' => $tank->fuel_level,
                'event_date' => $data['event_date'] ?? now(),
                'tank_moving_confirmation' => null
            ]);
        }
        
        if (empty($data['responsible_id'])) {
            $data['awaiting_confirmation'] = false;
        } else {
            $data['awaiting_confirmation'] = true;
            $this->notifyNewTankResponsible($tank);
        }

        if(!empty($data['externalOperations']) || !empty($data['externalDeletedOperations'])) {
            $this->handleFuelOperations($data['externalOperations'], $data['externalDeletedOperations']);
        }

        return [
            'data' => $data,
            // 'ignoreDataKeys' => []
        ];
    }

    public function afterDelete($entity)
    {
        FuelTankFlow::whereFuelTankId($entity->id)->delete();
        FuelTankTransferHistory::whereFuelTankId($entity->id)->delete();
    }

    public function validateTankNumberUnique(Request $request)
    {

        if(!$request->id) {
            return json_encode([
                'result' => !FuelTank::where(
                    'tank_number', $request->value
                )->exists()
            ],
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        } else {
            return json_encode([
                'result' => !FuelTank::where([
                    ['id', '<>', $request->id],
                    ['tank_number', $request->value]
                ])->exists()
            ],
                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }
    }

    public function moveFuelTank(Request $request)
    {
        $data = json_decode($request->data);
        $tank = $this->baseModel::findOrFail($data->id);

        if($tank->object_id == $data->object_id && $tank->responsible_id == $data->responsible_id) {
            return json_encode(['message'=>'Отказ. Попытка создать новую запись с текущими параметрами.']);
        }

        FuelTankTransferHistory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $tank->object_id,
            'object_id' => $data->object_id,
            'previous_responsible_id' => $tank->responsible_id,
            'responsible_id' => $data->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => $data->event_date,
            'tank_moving_confirmation' => (int)$tank->responsible_id === (int)$data->responsible_id ? true : null
            // 'tank_moving_confirmation' => (int)$tank->responsible_id === (int)$data->responsible_id
        ]);

        if(!empty($data->comment_movement_tmp)) {
            Comment::create([
                'commentable_id' => $tank->id,
                'commentable_type' => 'App\Models\TechAcc\FuelTank\FuelTank',
                'comment' => $data->comment_movement_tmp,
                'author_id' => Auth::user()->id,

            ]);
        }

        if((int)$tank->responsible_id !== (int)$data->responsible_id) {
            $needNotification = true;
            $tank->awaiting_confirmation = true;
            $tank->comment_movement_tmp = $data->comment_movement_tmp ?? null;
        } else {
            $tank->comment_movement_tmp = null;
        }

        $tank->object_id = $data->object_id;
        $tank->responsible_id = $data->responsible_id;

        $tank->save();

        if(!empty($needNotification)) {
            $this->notifyNewTankResponsible($tank);
        }

        return json_encode($tank);
    }

    public function confirmMovingFuelTank(Request $request)
    {
        $fuelTankId = json_decode($request->fuelTankId);
        $tank = $this->baseModel::findOrFail($fuelTankId);

        if (!$tank->awaiting_confirmation) {
            return;
        }

        $userId = App::environment('local') ? Auth::user()->id : $tank->responsible_id;
        $user = User::find($userId);

        (new FuelActions)->handleMovingFuelTankConfirmation($tank, $user);
    }

    public function getFuelTankConfirmationFormData(Request $request)
    {
        $fuelTankId = (int)json_decode($request->fuelTankId);
        $tank = $this->baseModel::findOrFail($fuelTankId);
        $responseData = new \stdClass();

        if(!$tank->awaiting_confirmation) {
            $responseData->status = 'not need confirmation';
            return json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }

        $responseData->status = 'need confirmation';
        $responseData->id = $tank->id;
        $responseData->tank_number = $tank->tank_number;
        $responseData->fuel_level = $tank->fuel_level;
        $responseData->object_name = ProjectObject::find($tank->object_id)->short_name;
        $responseData->responsible_name = User::find($tank->responsible_id)->full_name;

        return json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function notifyNewTankResponsible($tank)
    {
        (new FuelNotifications)->notifyNewFuelTankResponsibleUser($tank);
    }

    public function handleFuelOperations($operations, $deletedOperations, $newFuelTankId = null)
    {
        $fuelTankFlowController = new FuelTankFlowController;

        foreach($operations as $operation) {

            $data = (array)$operation;

            if(!empty($data['id']) && empty($data['guid'])) {

                $entity = FuelTankFlow::findOrFail($data['id']);

                DB::beginTransaction();
                    $beforeUpdateResult = $fuelTankFlowController->beforeUpdate($entity, $data);
                    $data = $beforeUpdateResult['data'];
                    $dataToUpdate = $this->getFuelFlowDataToStore($data);
                    $entity->update($dataToUpdate);
                    $fuelTankFlowController->afterUpdate($entity, $data, $dataToUpdate);
                DB::commit();

            } else {
                DB::beginTransaction();
                    if($newFuelTankId) {
                        $data['fuel_tank_id'] = $newFuelTankId;
                    }
                    $beforeStoreResult = $fuelTankFlowController->beforeStore($data);
                    $data = $beforeStoreResult['data'];
                    $dataToStore = $this->getFuelFlowDataToStore($data);
                    $entity = FuelTankFlow::create($dataToStore);
                    $fuelTankFlowController->afterStore($entity, $data, $dataToStore);
                DB::commit();
                return;
            }
        }

        foreach ($deletedOperations as $deletedOperation) {
            DB::beginTransaction();
                $entity = FuelTankFlow::findOrFail($deletedOperation);
                $fuelTankFlowController->beforeDelete($entity);
                $entity->delete();
                $fuelTankFlowController->afterDelete($entity);
            DB::commit();
        }
    }

    protected function getFuelFlowDataToStore($data)
    {
        unset($data['third_party_mark']);
        unset($data['fuelConsumerType']);
        unset($data['newAttachments']);
        unset($data['deletedAttachments']);
        unset($data['newComments']);
        unset($data['guid']);

        return $data;
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
        fuelTanksResponsibles =
            User::query()->active()
                ->whereIn('group_id', Group::FOREMEN)
                ->orWhere('group_id', 43)
                ->select(['id', 'user_full_name'])
                ->get();

        $this->additionalResources->
        companies =
            Company::all();

        $this->additionalResources->
        fuelFlowTypes =
            FuelTankFlowType::all();

        $this->additionalResources->
        fuelTanks =
            FuelTank::leftJoin('fuel_tank_transfer_histories', function($join) {
                $join->on('fuel_tank_transfer_histories.fuel_tank_id', '=', 'fuel_tanks.id')
                ->where('tank_moving_confirmation', true);
            })
            ->selectRaw('
                fuel_tanks.*, 
                MAX(event_date) as lastMovementConfirmationDate
            ')
            ->groupBy('fuel_tanks.id')
            ->get();

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
