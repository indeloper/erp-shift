<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowRemains;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankMovement;
use App\Models\User;
use App\Services\Common\FileSystemService;
use Carbon\Carbon;
use PDF;

class FuelReportController extends Controller
{
    public function fuelFlowMacroReportPageCore() {
        $routeNameFixedPart = 'building::tech_acc::fuel::reports::fuelFlowMacroReport::';
        $sectionTitle = 'Общая оборотная ведомость по топливным емкостям';
        $baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/reports/fuelFlowMacroReport';
 
        return view('1_base.desktop.index',
            $this->getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $baseBladePath)
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
        $sectionTitle = 'Перемещения топливных емкостей';
        $baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/reports/tanksMovementReport';
 
        return view('1_base.desktop.index',
            $this->getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $baseBladePath)
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

    public function getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $baseBladePath) {
        
        $componentsPath = $baseBladePath.'/desktop/components';
        $components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($componentsPath, $baseBladePath);

        return [
            'routeNameFixedPart' => $routeNameFixedPart,
            'sectionTitle' => $sectionTitle, 
            'baseBladePath' => $baseBladePath, 
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

    public function getCompanies() {
        $companies = Company::all();
        return response()->json($companies, 200, [], JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK);
    }


    public function fuelTankPeriodReportPageCore(Request $request) {
        $routeNameFixedPart = 'building::tech_acc::fuel::reports::fuelTankPeriodReport::';
        $sectionTitle = 'Отчет по топливу';
        $baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/reports/fuelTankPeriodReport';
 
        return view('1_base.desktop.index',
            $this->getReportPageCoreArray($routeNameFixedPart, $sectionTitle, $baseBladePath)
        );        
    }

    public function fuelTankPeriodReportData(Request $request) { 
        $dateFrom = Carbon::create($request->dateFrom)->toDateString();
        $dateTo = Carbon::create($request->dateTo)->toDateString();
        $fuelTank = FuelTank::find($request->fuelTankId);
        $fuelVolumeDateBegin = $this->getFuelVolumeDateBegin($dateFrom, $request->fuelTankId);
        $fuelFlows = FuelTankFlow::where([
            ['created_at', '>=', $dateFrom],
            ['created_at', '<=', $dateTo],
            ['fuel_tank_id', $request->fuelTankId],
            ['object_id', $request->objectId],
            ['responsible_id', $request->responsibleId],
        ]);

        $fuelFlows_clone1 = clone $fuelFlows;
        $fuelFlows_clone2  = clone $fuelFlows;

        $fuelIncomes = $fuelFlows_clone1
                        ->where('fuel_tank_flow_type_id', FuelTankFlowType::where('slug', 'income')->first()->id)
                        ->with('contractor')->get();
                        
        $fuelOutcomes = $fuelFlows_clone2
                        ->where('fuel_tank_flow_type_id', FuelTankFlowType::where('slug', 'outcome')->first()->id)
                        ->with('ourTechnic')->get();

        $pdf = PDF::loadView('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.report', 
            [
                'company' => Company::find($fuelTank->company_id)->name,
                'objectAdress' => ProjectObject::find($request->objectId)->address,
                'tank_number' => $fuelTank->tank_number,
                'dateFrom' => Carbon::create($request->dateFrom)->format('d.m.Y'),
                'dateTo' => Carbon::create($request->dateTo)->format('d.m.Y'),
                'fuelVolumeDateBegin' => $fuelVolumeDateBegin,
                'fuelIncomes' => $fuelIncomes,
                'fuelSumIncomes' => $fuelIncomes->sum('volume'),
                'fuelOutcomes' => $fuelOutcomes ,
                'fuelSumOutcomes' => $fuelOutcomes->sum('volume'),
                'fuelTankResponsible' => User::find($request->responsibleId),
                'carbonInstance' => new Carbon
            ]
        );
        return $pdf->stream('fuelTankPeriodReport.pdf');
        
    }

    public function getFuelVolumeDateBegin($dateFrom, $fuelTankId)
    {
        $fuelDateBegin=FuelTankFlowRemains::where([
            [
                'fuel_tank_id', $fuelTankId
            ],
            [
                'created_at', '<', $dateFrom
            ]
        ])->orderBy('id', 'desc')->first();

        if($fuelDateBegin)
            $fuelVolumeDateBegin = $fuelDateBegin->volume;
        else   
            $fuelVolumeDateBegin = 0;
        
        return $fuelVolumeDateBegin;
    }

}
