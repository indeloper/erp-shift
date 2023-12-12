<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Common\FileSystemService;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorAdditionalTypes;
use App\Models\Contractors\ContractorType;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowRemains;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use App\Services\Common\FilesUploadService;
use Carbon\Carbon;
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
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
        $this->modulePermissionsGroups = [17];
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
            ->when(!User::find(Auth::user()->id)->hasPermission('adjust_fuel_tank_remains'), function($query) {
                return $query->where('fuel_tank_flow_type_id', '<>', FuelTankFlowType::where('slug', 'adjustment')->first()->id);
            })
            ->when(!User::find(Auth::user()->id)->hasPermission('watch_any_fuel_tank_flows'), function($query) {
                return $query->where('responsible_id', Auth::user()->id);
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
        $tankCurrentFuelLevel = $tank->fuel_level;          
            
        if(FuelTankFlowType::find($data['fuel_tank_flow_type_id'])->slug === 'outcome') {
            $tank->fuel_level = round($tankCurrentFuelLevel - $data['volume']);
        }
    
        if(FuelTankFlowType::find($data['fuel_tank_flow_type_id'])->slug === 'income') {
            $tank->fuel_level = round($tankCurrentFuelLevel + $data['volume']);
        }
            
        if(FuelTankFlowType::find($data['fuel_tank_flow_type_id'])->slug === 'adjustment') {
            $tank->fuel_level = round($tankCurrentFuelLevel + $data['volume']);
        }
            
        $tank->save();

        unset($data['fuelConsumerType']);

        $data['author_id'] = Auth::user()->id;
        $data['responsible_id'] = $tank->responsible_id;
        $data['object_id'] = $tank->object_id;
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
        
        $tankCurrentFuelLevel = FuelTank::find($entity->fuel_tank_id)->fuel_level;

        $this->createFuelTankTransferHistory($entity->fuel_tank_id, $tankCurrentFuelLevel, $lastFuelTankTransferHistory, $entity->id, $entity->event_date);

    }
    
    public function beforeUpdate($entity, $data)
    {
        if ($entity->volume != $data['volume'] || $entity->fuel_tank_id != $data['fuel_tank_id']) {
            $fuelLevel = $this->syncFuelLevelData($entity, $data);
        }

        $historyLog = FuelTankTransferHistory::where(['fuel_tank_flow_id' => $entity->id])->orderByDesc('id')->first();

        $historyLog->fuel_tank_id = $data['fuel_tank_id'];
        $historyLog->event_date = $data['event_date'];

        if(isset($fuelLevel)) {
            $historyLog->fuel_level = $fuelLevel;
        }
        
        $historyLog->save();

        unset($data['fuelConsumerType']);
        $data['our_technic_id'] = $data['our_technic_id'] ?? null;
        $data['third_party_consumer'] = $data['third_party_consumer'] ?? null;
        
        return [
            'data' => $data,
        ];
    }

    public function beforeDelete($entity)
    {
        $tank = FuelTank::findOrFail($entity->fuel_tank_id);
        $tankCurrentFuelLevel = $tank->fuel_level;

        $fuelflowHistory = FuelTankTransferHistory::where('fuel_tank_flow_id', $entity->id)->orderByDesc('id')->first();
        if($fuelflowHistory) {
            $fuelflowHistory->delete();
        }
        
        if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'outcome') {
            $tank->fuel_level = round($tankCurrentFuelLevel + $entity->volume);
        }

        if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'income') {
            $tank->fuel_level = round($tankCurrentFuelLevel - $entity->volume);
        }

        if(FuelTankFlowType::find($entity->fuel_tank_flow_type_id)->slug === 'adjustment') {
            $tank->fuel_level = round($tankCurrentFuelLevel - $entity->volume);
        }
        
        $tank->save();
    }

    public function createFuelTankTransferHistory($fuelTankId, $fuel_level, $lastFuelTankTransferHistory, $fuel_tank_flow_id = null, $event_date = null)
    {
        $fuelTankFlow = FuelTankFlow::find($fuel_tank_flow_id);

        if(!$fuelTankFlow) {
            // Проверить, подумать. Кейс для удаления записи о движении топлива
            $fuelTankFlow = $lastFuelTankTransferHistory;
        }
        FuelTankTransferHistory::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $fuelTankId,
            'previous_object_id' => $lastFuelTankTransferHistory->previous_object_id,
            'object_id' => $fuelTankFlow->object_id,
            'previous_responsible_id' => $lastFuelTankTransferHistory->previous_responsible_id,
            'responsible_id' => $fuelTankFlow->responsible_id,
            'fuel_tank_flow_id' => $fuel_tank_flow_id,
            'fuel_level' => $fuel_level,
            'event_date' => $event_date ? $event_date : now()
        ]);
    }

    public function getFuelResponsibles()
    {
        return User::query()->active()
                ->orWhereIn('group_id', Group::FOREMEN)
                ->orderBy('last_name')
                ->get();
    }
   
    public function getFuelTanks()
    {
        return FuelTank::all();
    }

    public function getFuelContractors()
    {
        return Contractor::query()
            ->whereIn('main_type', ContractorType::where('name', 'Поставщик топлива')->pluck('id')->toArray())
            ->orWhereIn('id', ContractorAdditionalTypes::where('additional_type', ContractorType::where('name', 'Поставщик топлива')->first()->id)->pluck('contractor_id')->toArray() )
            ->get();
    }

    public function getFuelConsumers()
    {
        return OurTechnic::all();
    }

    public function getFuelFlowTypes()
    {
        if(User::find(Auth::user()->id)->hasPermission('adjust_fuel_tank_remains'))
        return FuelTankFlowType::all();

        return FuelTankFlowType::where('slug', '<>', 'adjustment')->get();
    }

    public function uploadFile(Request $request)
    {
        $uploadedFile = $request->files->all()['files'][0];
        $storage_name = 'fuel_flow';
        $storage_path = 'storage/docs/fuel_flow/';
        $documentable_id = $request->input('id');
        $documentable_type = 'App\Models\TechAcc\FuelTank\FuelTankFlow';

        [$fileEntry, $fileName] 
            = (new FilesUploadService)
            ->uploadFile($uploadedFile, $documentable_id, $documentable_type, $storage_name, $storage_path);

        return response()->json([
            'result' => 'ok',
            'fileEntryId' => $fileEntry->id,
            'filename' =>  $fileName,
            'fileEntry' => $fileEntry
        ], 200);
    }

    public function getThirdPartyFuelConsumers(Request $request)
    {
        $options = json_decode($request['data']);
        
        $thirdPartyFuelConsumers =
            (new FuelTankFlow)
                ->dxLoadOptions($options)
                ->whereNotNull('third_party_consumer')
                ->pluck('third_party_consumer')
                ->unique();

        return json_encode(array(
            "data" => $thirdPartyFuelConsumers
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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

}
