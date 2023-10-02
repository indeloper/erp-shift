<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Common\FileSystemService;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FuelTankFlowController extends StandardEntityResourceController
{
    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $basePath;
    protected $componentsPath;
    protected $components;

    public function __construct()
    {
        $this->baseModel = new FuelTankFlow();
        $this->routeNameFixedPart = 'building::tech_acc::fuel::fuelFlow::';
        $this->sectionTitle = 'Топливный журнал';
        $this->basePath = resource_path().'/views/tech_accounting/fuel/flow';
        $this->componentsPath = $this->basePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->basePath);
    }

    public function beforeStore($data)
    {
        $tank = FuelTank::findOrFail($data['fuel_tank_id']);
        $tankCurrentFuelLevel = $tank->fuel_level;
        if($data['type']==='Расход') 
            $tank->fuel_level = $tankCurrentFuelLevel - $data['volume'];
        if($data['type']==='Поступление') 
            $tank->fuel_level = $tankCurrentFuelLevel + $data['volume'];
        $tank->save();

        $data['author_id'] = Auth::user()->id;
        return $data;
    }

    public function beforeDelete($entity)
    {
        $tank = FuelTank::findOrFail($entity->fuel_tank_id);
        $tankCurrentFuelLevel = $tank->fuel_level;
        if($entity->type==='Расход') 
            $tank->fuel_level = $tankCurrentFuelLevel + $entity->volume;
        if($entity->type==='Поступление') 
            $tank->fuel_level = $tankCurrentFuelLevel - $entity->volume;
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
        return Contractor::all();
    }

    public function getFuelConsumers()
    {
        return OurTechnic::all();
    }
}
