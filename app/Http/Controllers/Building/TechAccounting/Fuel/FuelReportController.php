<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankMovement;
use App\Services\Common\FileSystemService;

class FuelReportController extends Controller
{
    public function fuelFlowMacroReportPageCore() {
        $routeNameFixedPart = 'building::tech_acc::fuel::reports::fuelFlowMacroReport::';
        $sectionTitle = 'Отчёт: оборотная ведомость по всем топливным ёмкостям';
        $basePath = resource_path().'/views/tech_accounting/fuel/reports/fuelFlowMacroReport';
 
        return view('tech_accounting.fuel.reports.fuelFlowMacroReport.desktop.index',
            $this->getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $basePath)
        );
    }

    public function fuelFlowMacroReportData(Request $request) {
        $options = json_decode($request['data']);
        $entities = (new FuelTankFlow)
            ->dxLoadOptions($options)
            ->orderBy('id', 'desc')
            ->get();

        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function tanksMovementReportPageCore(Request $request) {
        $routeNameFixedPart = 'building::tech_acc::fuel::reports::tanksMovementReport::';
        $sectionTitle = 'Отчёт: перемещения топливных ёмкостей';
        $basePath = resource_path().'/views/tech_accounting/fuel/reports/tanksMovementReport';
 
        return view('tech_accounting.fuel.reports.tanksMovementReport.desktop.index',
            $this->getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $basePath)
        );        
    }

    public function tanksMovementReportData(Request $request) {
        $options = json_decode($request['data']);
        $entities = (new FuelTankMovement)
            ->dxLoadOptions($options)
            ->orderBy('id', 'desc')
            ->get();

        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }


    // public function fuelFlowDetailedReportPageCore(Request $request) {
        
    // }

    // public function fuelFlowDetailedReportData(Request $request) {
        
    // }

    public function getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $basePath) {
        
        $componentsPath = $basePath.'/desktop/components';
        $components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($componentsPath, $basePath);

        return [
            'routeNameFixedPart' => $routeNameFixedPart,
            'sectionTitle' => $sectionTitle, 
            'basePath' => $basePath, 
            'components' => $components
        ];
    }

    public function getProjectObjects() {
        $objects = ProjectObject::query()
            ->whereNotNull('short_name')
            // ->where('is_participates_in_material_accounting', '>', 0)
            ->addSelect(['project_objects.id AS id',
                'short_name', 'project_objects.name AS object_name'
                ])
            ->orderBy('short_name')->get();

        return response()->json($objects, 200, [], JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK);
    }
}
