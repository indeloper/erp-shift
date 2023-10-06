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
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankFlowTypes;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use App\Services\Common\FilesUploadService;
use Illuminate\Support\Facades\Auth;

class FuelTankFlowController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new FuelTankFlow();
        $this->routeNameFixedPart = 'building::tech_acc::fuel::fuelFlow::';
        $this->sectionTitle = 'Топливный журнал';
        $this->baseBladePath = resource_path().'/views/tech_accounting/fuel/flow';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }

    public function beforeStore($data)
    {
        $tank = FuelTank::findOrFail($data['fuel_tank_id']);
        $tankCurrentFuelLevel = $tank->fuel_level;
        
        if(FuelTankFlowType::find($data['fuel_tank_flow_type_id'])->slug === 'outcome')
            $tank->fuel_level = round($tankCurrentFuelLevel - $data['volume'], 3);
        if(FuelTankFlowType::find($data['fuel_tank_flow_type_id'])->slug === 'income') 
            $tank->fuel_level = round($tankCurrentFuelLevel + $data['volume'], 3);
        if(FuelTankFlowType::find($data['fuel_tank_flow_type_id'])->slug === 'adjustment') 
            $tank->fuel_level = round($tankCurrentFuelLevel + $data['volume'], 3);
        $tank->save();

        $data['author_id'] = Auth::user()->id;
        
        return [
            'data' => $data, 
            'ignoreDataKeys' => ['newAttachments']
        ];
    }

    public function afterStore($entity, $data)
    {
        if(!empty($data['newAttachments']))
            (new FilesUploadService)->attachFiles($entity, $data['newAttachments']);
    }

    public function beforeDelete($entity)
    {
        $tank = FuelTank::findOrFail($entity->fuel_tank_id);
        $tankCurrentFuelLevel = $tank->fuel_level;
        if($entity->type==='Расход') 
            $tank->fuel_level = round($tankCurrentFuelLevel + $entity->volume, 3);
        if($entity->type==='Поступление') 
            $tank->fuel_level = round($tankCurrentFuelLevel - $entity->volume, 3);
        $tank->save();
    }

    public function getFuelResponsibles()
    {
        return User::query()->active()
                ->orWhereIn('group_id', Group::FOREMEN)
                // ->select(['id', 'first_name', 'last_name', 'patronymic'])
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
        return FuelTankFlowType::all();
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


}
