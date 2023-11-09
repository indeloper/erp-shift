<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Company\Company;
use App\Models\Group;
use App\Models\Notification;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankMovement;
use App\Models\TechAcc\FuelTank\FuelTankTransferHystory;
use App\Models\User;
use App\Services\Common\FileSystemService;
use Illuminate\Support\Facades\Auth;

class FuelTankController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new FuelTank;
        $this->routeNameFixedPart = 'building::tech_acc::fuel::tanks::';
        $this->sectionTitle = 'Топливные емкости';
        $this->baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/objects';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);
        
        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->selectRaw(
                '*, 
                (SELECT MAX(`event_date`) from `fuel_tank_flows` where `fuel_tank_flows`.`fuel_tank_id` = `fuel_tanks`.`id`) 
                as max_event_date'
            )
            ->get();
    
        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
    
    public function afterStore($tank, $data, $dataToStore)
    {
        FuelTankMovement::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'object_id' => $tank->object_id,
            'fuel_level' => 0
        ]);

        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'object_id' => $tank->object_id,
            'responsible_id' => $tank->responsible_id,
            'fuel_level' => 0,
            'event_date' => $data['event_date'] ?? now()
        ]);
    }

    public function beforeUpdate($tank, $data)
    {
        FuelTankMovement::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $tank->object_id,
            'object_id' => $data['object_id'] ?? $tank->object_id ?? null,
            'fuel_level' => $tank->fuel_level
        ]);

        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $tank->object_id,
            'object_id' => $data['object_id'] ?? $tank->object_id ?? null,
            'previous_responsible_id' => $tank->responsible_id,
            'responsible_id' => $data['responsible_id'] ?? $tank->responsible_id ?? null,
            'fuel_level' => $tank->fuel_level,
            'event_date' => $data['event_date'] ?? now()
        ]);

        return [
            'data' => $data, 
            // 'ignoreDataKeys' => []
        ];
    }

    public function getFuelTanksResponsibles()
    {
        return User::query()->active()
                ->whereIn('group_id', Group::FOREMEN)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
    }

    public function getCompanies() {
        $companies = Company::all();
        return response()->json($companies, 200, [], JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK);
    }

    public function getProjectObjects(Request $request)
    {
        $options = json_decode($request['data']);
        
        $objects = (new ProjectObject)
            ->where('is_participates_in_material_accounting', 1)
            ->whereNotNull('short_name')
            ->orderBy('short_name')
            ->get()
            ->toArray();

        return json_encode(
            $objects
        ,
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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

        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $tank->object_id,
            'object_id' => $data->object_id,
            'previous_responsible_id' => $tank->responsible_id,
            'responsible_id' => $data->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => $data->event_date
        ]);
        
        $tank->object_id = $data->object_id;
        $tank->responsible_id = $data->responsible_id;
        $tank->awaiting_confirmation = true;
        $tank->save();

        $notificationHook = 'notificationHook_confirmFuelTankRecieve-id-'.$tank->id.'_endNotificationHook';
        $notificationText = 
            'Подтвердите получение топливной емкости № '
            .$tank->tank_number.' на объекте '.ProjectObject::find($tank->object_id)->short_name
            .' '.$notificationHook;

        Notification::create([
            'name' => $notificationText,
            'user_id' => $tank->responsible_id,
            'type' => 0,
        ]);

        return json_encode($tank);
    }

    public function confirmMovingFuelTank(Request $request)
    {
        $fuelTankId = json_decode($request->fuelTankId);
        $tank = $this->baseModel::findOrFail($fuelTankId);

        $lastTankTransferHistory = FuelTankTransferHystory::query()
            ->whereNull('fuel_tank_flow_id')
            ->whereNull('tank_moving_confirmation')
            ->orderByDesc('id')
            ->firstOrFail();

        FuelTankTransferHystory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'previous_object_id' => $lastTankTransferHistory->previous_object_id,
            'object_id' => $tank->object_id,
            'previous_responsible_id' => $lastTankTransferHistory->previous_responsible_id,
            'responsible_id' => $tank->responsible_id,
            'fuel_level' => $tank->fuel_level,
            'event_date' => now(),
            'tank_moving_confirmation' => true
        ]);

        $tank->awaiting_confirmation = false;
        $tank->save();
    }

    public function getFuelTankConfirmationFormData(Request $request)
    {
        $fuelTankId = (int)json_decode($request->fuelTankId);
        $tank = $this->baseModel::findOrFail($fuelTankId);
        $responseData = new \stdClass();
        
        if(!$tank->awaiting_confirmation) 
        {
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
}
