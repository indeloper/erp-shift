<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel\Reports;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use Illuminate\Http\Request;

class FuelTanksMovementsReportController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Перемещения топливных емкостей';
        $this->baseModel = new FuelTankTransferHistory();
        $this->routeNameFixedPart = 'building::tech_acc::fuel::reports::tanksMovementReport::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/reports/tanksMovementReport';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = $this->getModuleComponents();
        $this->modulePermissionsGroups = [17];
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->whereNotNull('tank_moving_confirmation')
            ->get();

        return json_encode([
            'data' => $entities,
        ],
            JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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
        fuelTanks =
            FuelTank::all();
    }
}
