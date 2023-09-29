<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Common\FileSystemService;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\TankFuelFlow;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;

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
        $this->baseModel = new TankFuelFlow();
        $this->routeNameFixedPart = 'building::tech_acc::fuel::fuelFlow::';
        $this->sectionTitle = 'Топливный журнал';
        $this->basePath = resource_path().'/views/tech_accounting/fuel/flow';
        $this->componentsPath = $this->basePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->basePath);
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
