<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankFlowRemains;
use App\Models\TechAcc\FuelTank\FuelTankFlowType;
use App\Models\TechAcc\FuelTank\FuelTankMovement;
use App\Models\TechAcc\FuelTank\FuelTankTransferHystory;
use App\Models\User;
use App\Services\Common\FileSystemService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function fuelFlowPeriodReport(Request $request)
    {
        $currentMonthBegin = Carbon::create($request->year.'-'.$request->month);
        $cloneCurrentMonthBegin = clone $currentMonthBegin;
        $nextMonthBegin = $cloneCurrentMonthBegin->addMonth();
        $currentMonthEnd = $nextMonthBegin->subDay();
        // $responsibleId = Auth::user()->id;
        // $responsibleId = 33;  

        $options = json_decode($request['loadOptions']);

        unset($options->sort);

        $fuelTankFlowsIds = (new FuelTankFlow)
            ->dxLoadOptions($options)
            ->where([
                ['event_date', '>=', $currentMonthBegin],
                ['event_date', '<=', $currentMonthEnd],
            ])
            ->pluck('id')
            ->unique()
            ->toArray();

        $filteredResponsiblesArr = $this->getFilteredResponsiblesArr($options->filter);

        $baseReportArraySource = FuelTankTransferHystory::query();
            
        $baseReportArraySource_ = clone $baseReportArraySource;

        $baseReportArray = $baseReportArraySource
            ->where([
                ['fuel_tank_transfer_hystories.event_date', '>=', $currentMonthBegin],
                ['fuel_tank_transfer_hystories.event_date', '<=', $currentMonthEnd],
            ])
            ->whereIn('fuel_tank_transfer_hystories.fuel_tank_flow_id', $fuelTankFlowsIds)
            ->orWhere('fuel_tank_transfer_hystories.fuel_tank_flow_id', null)
            ->leftJoin('fuel_tank_flows', 'fuel_tank_transfer_hystories.fuel_tank_flow_id', '=', 'fuel_tank_flows.id')
            ->leftJoin('fuel_tank_flow_types', 'fuel_tank_flows.fuel_tank_flow_type_id', '=', 'fuel_tank_flow_types.id')
            ->leftJoin('contractors', 'fuel_tank_flows.contractor_id', '=', 'contractors.id')
            ->leftJoin('our_technics', 'fuel_tank_flows.our_technic_id', '=', 'our_technics.id')
            ->leftJoin('companies', 'fuel_tank_flows.company_id', '=', 'companies.id')
            ->leftJoin('project_objects', 'fuel_tank_flows.object_id', '=', 'project_objects.id')
            ->leftJoin('users', 'fuel_tank_flows.responsible_id', '=', 'users.id')
            ->leftJoin('fuel_tanks', 'fuel_tank_flows.fuel_tank_id', '=', 'fuel_tanks.id')
            ->orderBy('fuel_tank_transfer_hystories.responsible_id') 
            ->orderBy('fuel_tank_transfer_hystories.fuel_tank_id')
            ->orderBy('fuel_tank_transfer_hystories.object_id')
            ->orderBy('fuel_tank_transfer_hystories.event_date')
            ->orderBy('fuel_tank_transfer_hystories.id')
            ->get(['fuel_tank_transfer_hystories.responsible_id as responsible_id',
                'fuel_tank_transfer_hystories.previous_responsible_id as previos_responsible_id',
                'fuel_tank_transfer_hystories.fuel_tank_id',
                'fuel_tank_transfer_hystories.object_id',
                'fuel_tank_transfer_hystories.previous_object_id',
                'fuel_tank_transfer_hystories.event_date',
                'fuel_tank_transfer_hystories.fuel_level',
                'fuel_tank_flows.volume',
                'fuel_tank_flow_types.slug as fuel_tank_flow_type_slug',
                'contractors.short_name as contractor',
                'our_technics.name as fuel_consumer',
                'companies.name as company',
                'project_objects.address as adress',
                'fuel_tanks.tank_number',
                'fuel_tank_flows.document',
                DB::raw('
                    SUM(
                        IF(volume IS NULL, 1, 0))
                        OVER 
                            (
                                ORDER BY fuel_tank_transfer_hystories.responsible_id, 
                                fuel_tank_transfer_hystories.fuel_tank_id,
                                fuel_tank_transfer_hystories.object_id, 
                                fuel_tank_transfer_hystories.event_date,
                                fuel_tank_transfer_hystories.id
                            ) AS group_marker
                        ')
            ])
            ->groupBy(['responsible_id', 'fuel_tank_id', 'object_id', 'group_marker', 'fuel_tank_flow_type_slug'])->toArray();
            
        $fuelTanksIncludedinReportIds = $baseReportArraySource_->pluck('fuel_tank_id')->unique()->toArray();
        $fuelTanksNotIncludedinReport = 
            FuelTank::query()
            ->whereNotIn('id', $fuelTanksIncludedinReportIds)
            ->when(!empty($filteredResponsiblesArr), function($query) use($filteredResponsiblesArr) {
                $query->whereIn('responsible_id', $filteredResponsiblesArr);
            })
            ->get();
// dd($baseReportArray);
        // return view('tech_accounting.fuel.tanks.reports.fuelFlowPersonalPeriodReport.pdfTemlates.reportTemplate',
        //     [
        //         'baseReportArray' => $baseReportArray,
        //         'dateFrom' => $currentMonthBegin->format('d.m.Y'),
        //         'dateTo' => $nextMonthBegin->subDay()->format('d.m.Y'),
        //         'companyModelInstance' => new Company,
        //         'fuelTankModelInstance' => new FuelTank,
        //         'objectModelInstance' => new ProjectObject,
        //         'userModelInstance' => new User,
        //         'employeeModelInstance' => new Employee,
        //         'employees1cPostModelInstance' => new Employees1cPost,
        //         'carbonInstance' => new Carbon,
        //         'reportControllerInstance' => $this,
        //     ]
        // );

            foreach($fuelTanksNotIncludedinReport as $notIncludedTank){
                 if (!isset($baseReportArray[$notIncludedTank->responsible_id])){
                    $baseReportArray[$notIncludedTank->responsible_id] = [];
                    $baseReportArray[$notIncludedTank->responsible_id][$notIncludedTank->id] = [];                    
                } else {
                    if (!isset($baseReportArray[$notIncludedTank->responsible_id][$notIncludedTank->id])){
                        $baseReportArray[$notIncludedTank->responsible_id][$notIncludedTank->id] = [];
                    }
                }

                $baseReportArray[$notIncludedTank->responsible_id][$notIncludedTank->id][$notIncludedTank->object_id] = [
                    0 => ["notIncludedTank" => []]
                ];
            }

            $pdf = PDF::loadView('tech_accounting.fuel.tanks.reports.fuelFlowPersonalPeriodReport.pdfTemlates.reportTemplate', 
                [
                    'baseReportArray' => $baseReportArray,
                    'dateFrom' => $currentMonthBegin->format('d.m.Y'),
                    'dateTo' => $nextMonthBegin->subDay()->format('d.m.Y'),
                    'companyModelInstance' => new Company,
                    'fuelTankModelInstance' => new FuelTank,
                    'objectModelInstance' => new ProjectObject,
                    'userModelInstance' => new User,
                    'employeeModelInstance' => new Employee,
                    'employees1cPostModelInstance' => new Employees1cPost,
                    'carbonInstance' => new Carbon,
                    'reportControllerInstance' => $this,
                ]
            );

        return $pdf->stream('Отчет по дизельному топливу '.$currentMonthBegin->format('d.m.Y'). '-' .$nextMonthBegin->subDay()->format('d.m.Y').'.pdf');
    }

    public function getSummaryDataFuelFlowPeriodReport($objectTransferGroups, $fuelTankId, $responsibleId, $globalDateFrom, $globalDateTo)
    {
        if(isset($objectTransferGroups['notIncludedTank'])) {
            $fuelTankFuelLevel = FuelTank::find($fuelTankId)->fuel_level;
            return [
                'fuelLevelPeriodStart' => $fuelTankFuelLevel,
                'fuelLevelPeriodFinish' => $fuelTankFuelLevel,
                'fuelPeriodMovementsOrResponsibleChanges' => [],
                'dateFrom' => $globalDateFrom,
                'dateTo' => $globalDateTo,
            ];
        }
        $eventDates = [];
        array_walk_recursive($objectTransferGroups, function($value, $key) use(&$eventDates) {
            if($key === 'event_date')
            $eventDates[] = $value;
        });

        $dateToTmp = Carbon::create(max($eventDates));
        if( 
            FuelTankTransferHystory::where([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>=',  $dateToTmp],
                ['event_date', '<',  Carbon::create($globalDateTo)],
            ])
            ->whereNull('fuel_tank_flow_id')
            ->exists()
        ) {
            $dateTo = FuelTankTransferHystory::where([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '>=',  $dateToTmp],
                ['event_date', '<',  Carbon::create($globalDateTo)],
            ])
            ->whereNull('fuel_tank_flow_id')
            ->first()
            ->event_date;
        } else {
            $dateTo = $globalDateTo;
        }

        $dateFrom = Carbon::create(min($eventDates));
        $dateTo = Carbon::create($dateTo);

        $fuelPeriodPreviousTransaction = FuelTankTransferHystory::where([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '<',  $dateFrom],
        ])
        ->orderBy('id', 'desc')
        ->first();

        if($fuelPeriodPreviousTransaction) {
            $fuelLevelPeriodStart = $fuelPeriodPreviousTransaction->fuel_level;
        } else {
            $fuelLevelPeriodStart = 0;
        }

        $fuelPeriodLastTransaction = $fuelPeriodPreviousTransaction = FuelTankTransferHystory::where([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '>=',  $dateFrom],
            ['event_date', '<=',  $dateTo],
        ])
        ->orderBy('id', 'desc')
        ->first();

        if($fuelPeriodLastTransaction) {
            $fuelLevelPeriodFinish = $fuelPeriodLastTransaction->fuel_level;
        } else {
            $fuelLevelPeriodFinish = $fuelLevelPeriodStart;
        }

        $fuelPeriodMovementsOrResponsibleChanges = FuelTankTransferHystory::where([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '>=',  $dateFrom],
            ['event_date', '<=',  $dateTo],
        ])
        ->whereNull('fuel_tank_flow_id')
        ->get()
        ->toArray();
        
        return [
            'fuelLevelPeriodStart' => $fuelLevelPeriodStart,
            'fuelLevelPeriodFinish' => $fuelLevelPeriodFinish,
            'fuelPeriodMovementsOrResponsibleChanges' => $fuelPeriodMovementsOrResponsibleChanges,
            'dateFrom' => $dateFrom->format('d.m.Y'),
            'dateTo' => $dateTo->format('d.m.Y'),
        ];

    }

    public function getFilteredResponsiblesArr($filter)
    {
        if(!str_contains(json_encode($filter), 'responsible_id'))
        return [];

        $filter = (array)$filter;
        $resultArr = [];
        $i=0;
        array_walk_recursive($filter, function($value, $key) use(&$resultArr, &$i) {
            if($value === 'responsible_id') {
                $i++;
            }
            if($i) {
                $i++;
                if($i===4) {
                    $resultArr[] = $value;
                    $i=0;
                }
            }
        });

        return $resultArr;
    }

}
