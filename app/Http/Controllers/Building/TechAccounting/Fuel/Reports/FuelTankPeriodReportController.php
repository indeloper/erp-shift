<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Company\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankTransferHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;

class FuelTankPeriodReportController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Отчет по топливу';
        $this->routeNameFixedPart = 'building::tech_acc::fuel::reports::fuelTankPeriodReport::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/reports/fuelTankPeriodReport';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = $this->getModuleComponents();
        $this->modulePermissionsGroups = [17];
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

        $this->additionalResources->
        fuelTanksResponsibles =
            User::query()->active()
                ->whereIn('group_id', Group::FOREMEN)
                ->orWhere('group_id', 43)
                ->select(['id', 'user_full_name'])
                ->get();
    }

    public function getPdf(Request $request)
    {
        [$globalDateFrom, $globalDateTo, $globalDateToNextDay] = $this->getGlobalDatesFromTo($request);

        $options = json_decode($request['loadOptions']);

        unset($options->sort);

        $fuelTankFlowsIds = (new FuelTankFlow)
            ->dxLoadOptions($options)
            ->where([
                ['event_date', '>=', $globalDateFrom],
                ['event_date', '<=', $globalDateToNextDay],
            ])
            ->when(!User::find(Auth::user()->id)->hasPermission('watch_any_fuel_tank_flows'), function($query) {
                return $query->where('responsible_id', Auth::user()->id);
            })
            ->pluck('id')
            ->unique()
            ->toArray();

        $baseReportArraySource = FuelTankTransferHistory::query()
            ->where([
                ['fuel_tank_transfer_histories.event_date', '>=', $globalDateFrom],
                ['fuel_tank_transfer_histories.event_date', '<=', $globalDateToNextDay],
            ])
            ->whereIn('fuel_tank_transfer_histories.fuel_tank_flow_id', $fuelTankFlowsIds);

        $baseReportArraySource_ = clone $baseReportArraySource;

        $baseReportArray = $baseReportArraySource
            ->leftJoin('fuel_tank_flows', 'fuel_tank_transfer_histories.fuel_tank_flow_id', '=', 'fuel_tank_flows.id')
            ->leftJoin('fuel_tank_flow_types', 'fuel_tank_flows.fuel_tank_flow_type_id', '=', 'fuel_tank_flow_types.id')
            ->leftJoin('contractors', 'fuel_tank_flows.contractor_id', '=', 'contractors.id')
            ->leftJoin('our_technics', 'fuel_tank_flows.our_technic_id', '=', 'our_technics.id')
            ->leftJoin('companies', 'fuel_tank_flows.company_id', '=', 'companies.id')
            ->leftJoin('project_objects', 'fuel_tank_flows.object_id', '=', 'project_objects.id')
            ->leftJoin('users', 'fuel_tank_flows.responsible_id', '=', 'users.id')
            ->leftJoin('fuel_tanks', 'fuel_tank_flows.fuel_tank_id', '=', 'fuel_tanks.id')
            ->orderBy('fuel_tank_transfer_histories.responsible_id')
            ->orderBy('fuel_tank_transfer_histories.fuel_tank_id')
            ->orderBy('fuel_tank_transfer_histories.object_id')
            ->orderBy('fuel_tank_flow_types.id')
            ->orderBy('fuel_tank_transfer_histories.event_date')
            ->orderBy('fuel_tank_transfer_histories.id')
            ->get(['fuel_tank_transfer_histories.responsible_id as responsible_id',
                'fuel_tank_transfer_histories.tank_moving_confirmation AS tank_moving_confirmation',
                'fuel_tank_transfer_histories.previous_responsible_id as previos_responsible_id',
                'fuel_tank_transfer_histories.fuel_tank_id',
                'fuel_tank_transfer_histories.object_id',
                'fuel_tank_transfer_histories.previous_object_id',
                'fuel_tank_transfer_histories.event_date',
                'fuel_tank_transfer_histories.fuel_level',
                'fuel_tank_flows.volume',
                'fuel_tank_flow_types.slug as fuel_tank_flow_type_slug',
                'contractors.short_name as contractor',
                DB::raw('CASE WHEN fuel_tank_flows.our_technic_id THEN our_technics.name ELSE fuel_tank_flows.third_party_consumer END as fuel_consumer'),
                // 'our_technics.name as fuel_consumer',
                'companies.name as company',
                'project_objects.address as adress',
                'fuel_tanks.tank_number',
                'fuel_tank_flows.document',
                'fuel_tank_flows.author_id',
                DB::raw('
                    SUM(
                        IF(tank_moving_confirmation = 1, 1, 0))
                        OVER
                            (
                                ORDER BY fuel_tank_transfer_histories.responsible_id,
                                fuel_tank_transfer_histories.fuel_tank_id,
                                fuel_tank_transfer_histories.object_id,
                                fuel_tank_flow_types.id,
                                fuel_tank_transfer_histories.event_date,
                                fuel_tank_transfer_histories.id
                            ) AS group_marker
                        ')
                // DB::raw('
                //     SUM(
                //         IF(volume IS NULL, 1, 0))
                //         OVER
                //             (
                //                 ORDER BY fuel_tank_transfer_histories.responsible_id,
                //                 fuel_tank_transfer_histories.fuel_tank_id,
                //                 fuel_tank_transfer_histories.object_id,
                //                 fuel_tank_flow_types.id,
                //                 fuel_tank_transfer_histories.event_date,
                //                 fuel_tank_transfer_histories.id
                //             ) AS group_marker
                //         ')
            ])
            ->groupBy(['responsible_id', 'fuel_tank_id', 'object_id', 'group_marker', 'fuel_tank_flow_type_slug'])->toArray();

        $fuelTanksIncludedinReportIds = $baseReportArraySource_->pluck('fuel_tank_id')->unique()->toArray();

        $filteredByResponsiblesArr = $this->getFilteredArray($options->filter, 'responsible_id');
        $filteredByTankArr = $this->getFilteredArray($options->filter, 'fuel_tank_id');
        $filteredByCompanyArr = $this->getFilteredArray($options->filter, 'company_id');
        $filteredByObjectArr = $this->getFilteredArray($options->filter, 'object_id');

        if(
            !User::find(Auth::user()->id)->hasPermission('watch_any_fuel_tank_flows')
            && !in_array(Auth::user()->id, $filteredByResponsiblesArr)
        ) {
            $filteredByResponsiblesArr[] = Auth::user()->id;
        }

        $fuelTanksNotIncludedinReport =
            FuelTank::query()
            ->whereNotIn('id', $fuelTanksIncludedinReportIds)
            ->when(!empty($filteredByResponsiblesArr), function($query) use($filteredByResponsiblesArr) {
                $query->whereIn('responsible_id', $filteredByResponsiblesArr);
            })
            ->when(!empty($filteredByTankArr), function($query) use($filteredByTankArr) {
                $query->whereIn('id', $filteredByTankArr);
            })
            ->when(!empty($filteredByCompanyArr), function($query) use($filteredByCompanyArr) {
                $query->whereIn('id', $filteredByCompanyArr);
            })
            ->get();

        foreach($fuelTanksNotIncludedinReport as $notIncludedTank){
            $baseReportArray[$notIncludedTank->responsible_id][$notIncludedTank->id][$notIncludedTank->object_id] = [
                0 => ["notIncludedTank" => []]
            ];
        }

        // добавляем бочки, у которых сменился объект, но операций на новом объекте не было
        $includedTanksInReport = FuelTank::whereIn('id', $fuelTanksIncludedinReportIds)
            ->when(!empty($filteredByResponsiblesArr), function($query) use($filteredByResponsiblesArr) {
                $query->whereIn('responsible_id', $filteredByResponsiblesArr);
            })
            ->when(!empty($filteredByTankArr), function($query) use($filteredByTankArr) {
                $query->whereIn('id', $filteredByTankArr);
            })
            ->when(!empty($filteredByCompanyArr), function($query) use($filteredByCompanyArr) {
                $query->whereIn('id', $filteredByCompanyArr);
            })
            ->get();

        foreach($includedTanksInReport as $includedTank)
        {
            if(!isset($baseReportArray[$includedTank->responsible_id][$includedTank->id][$includedTank->object_id])) {
                $baseReportArray[$includedTank->responsible_id][$includedTank->id][$includedTank->object_id] = [
                    0 => ["notIncludedTank" => []]
                ];
            }
        }

        $transtionPeriodTanksList = $this->getTransitionPeriodTanksList($globalDateFrom); 

        foreach($transtionPeriodTanksList as $tank) {
            if(!empty($filteredByTankArr) && !in_array($tank['fuel_tank_id'], $filteredByTankArr)) {
                continue;
            }
            if(!empty($filteredByResponsiblesArr) && !in_array($tank['responsible_id'], $filteredByResponsiblesArr)) {
                continue;
            }
            if(!empty($filteredByObjectArr) && !in_array($tank['object_id'], $filteredByObjectArr)) {
                continue;
            }

            $baseReportArray[$tank['responsible_id']][$tank['fuel_tank_id']][$tank['object_id']] = [
                0 => ["notIncludedTank" => []]
            ];
        }

        if(!count($baseReportArray)) {

            return view('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.emptyReportTemplate',
                [
                    'dateFrom' => $globalDateFrom->format('d.m.Y'),
                    'dateTo' => $globalDateTo->format('d.m.Y'),
                    'responsiblesFilter' => User::whereIn('id', $filteredByResponsiblesArr)->get(),
                    'tanksFilter' => FuelTank::whereIn('id', $filteredByTankArr)->get(),
                    'objectsFilter' => ProjectObject::whereIn('id', $filteredByObjectArr)->get(),
                ]
            );
        }

        $pdf = PDF::loadView('tech_accounting.fuel.tanks.reports.fuelTankPeriodReport.pdfTemlates.reportTemplate',
            [
                'baseReportArray' => $baseReportArray,
                'dateFrom' => $globalDateFrom->format('d.m.Y'),
                'dateTo' => $globalDateTo->format('d.m.Y'),
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

        return $pdf->stream(
            'Отчет по дизельному топливу '
            .$globalDateFrom->format('d.m.Y'). '-'
            .$globalDateTo->format('d.m.Y')
            .'.pdf');
    }

    public function getTransitionPeriodTanksList($globalDateFrom)
    {
        $tanksList = [];
        foreach(FuelTank::all() as $fuelTank) {
            $lastTranferHistory = FuelTankTransferHistory::where([
                ['fuel_tank_id', $fuelTank->id],
                ['event_date', '<', $globalDateFrom]
            ])->orderByDesc('event_date')->first();
 
            $tanksList[] = [
                'responsible_id' => $lastTranferHistory->responsible_id ??  $fuelTank->responsible_id,
                'fuel_tank_id' => $fuelTank->id,
                'object_id' => $lastTranferHistory->object_id ?? $fuelTank->object_id,
            ];
        }
        return $tanksList;
    }

    public function getGlobalDatesFromTo($request)
    {
        if(isset($request->dateFrom) && isset($request->dateTo))
        {
            $globalDateFrom = Carbon::create(Carbon::create($request->dateFrom)->toDateString());
            $globalDateTo = Carbon::create(Carbon::create($request->dateTo)->toDateString());
        } else {
            $globalDateFrom = Carbon::create($request->year.'-'.$request->month);
            $cloneGlobalDateFrom = clone $globalDateFrom;
            $globalDateTo = $cloneGlobalDateFrom->addMonth()->subDay();
        }

        $cloneGlobalDateTo = clone $globalDateTo;
        $globalDateToNextDay = $cloneGlobalDateTo->addDay();

        return [$globalDateFrom, $globalDateTo, $globalDateToNextDay];
    }

    public function getSummaryDataFuelFlowPeriodReport($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        [$dateFrom, $dateTo] = $this->getPeriodDatesFromTo($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo);

        [$fuelLevelPeriodStart, $fuelLevelPeriodFinish] = $this->getPeriodFuelRemains($responsibleId, $fuelTankId, $objectId, $dateFrom, $dateTo);

        $confirmedTankMovements = FuelTankTransferHistory::query()
        ->where([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '>=',  $dateFrom],
            ['event_date', '<=',  $dateTo],
            ['responsible_id', $responsibleId],
            ['tank_moving_confirmation', '<>', null]
        ])
        ->orWhere([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '>=',  $dateFrom],
            ['event_date', '<=',  $dateTo],
            ['previous_responsible_id', $responsibleId],
            ['tank_moving_confirmation', '<>', null]
        ])
        ->whereNotNull('object_id')
        ->whereNotNull('previous_object_id')
        ->orderBy('event_date')
        ->get()
        ->toArray();

        return [
            'fuelLevelPeriodStart' => $fuelLevelPeriodStart,
            'fuelLevelPeriodFinish' => $fuelLevelPeriodFinish,
            'confirmedTankMovements' => $confirmedTankMovements,
            'dateFrom' => $dateFrom->format('d.m.Y'),
            'dateTo' => $dateTo->format('d.m.Y'),
        ];

    }

    public function getPeriodDatesFromTo($objectTransferGroups, $responsibleId, $fuelTankId, $objectId, $globalDateFrom, $globalDateTo)
    {
        $eventDates = [];
        array_walk_recursive($objectTransferGroups, function($value, $key) use(&$eventDates) {
            if($key === 'event_date')
            $eventDates[] = $value;
        });

        if(empty($eventDates)) {
            //Проверка если последняя запись в журнале - смена ответственного
            $lastTankObjectOrResponsibleChanged = FuelTankTransferHistory::where([
                ['fuel_tank_id', $fuelTankId],
                ['event_date', '<', Carbon::create($globalDateTo)->addday()],
                ['event_date', '>=', Carbon::create($globalDateFrom)],
                ['object_id', $objectId],
                ['responsible_id', $responsibleId],
                ['tank_moving_confirmation', true]
            ])
            ->orderByDesc('id')->first();

            if($lastTankObjectOrResponsibleChanged) {
                $dateFromTmp = $lastTankObjectOrResponsibleChanged->event_date;
            } else {
                $dateFromTmp = Carbon::create($globalDateFrom);
            }

            $dateToTmp = Carbon::create($globalDateTo);
        } else {
            $dateFromTmp = Carbon::create(min($eventDates));
            $dateToTmp = Carbon::create(max($eventDates));
        }

        $to = FuelTankTransferHistory::where([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '>=',  $dateToTmp],
            ['event_date', '<',  Carbon::create($globalDateTo)->addday()],
            ['previous_object_id', $objectId],
            ['previous_responsible_id', $responsibleId],
            ['tank_moving_confirmation', true]
        ])->orderByDesc('id')->first();

        if($to) {
            $dateTo = $to->event_date;
        } else {
            $dateTo = $globalDateTo;
        }

        $from = FuelTankTransferHistory::where([
            ['fuel_tank_id', $fuelTankId],
            ['event_date', '<=',  $dateFromTmp],
            ['event_date', '>=', Carbon::create($globalDateFrom)],
            ['object_id', $objectId],
            ['responsible_id', $responsibleId],
            ['tank_moving_confirmation', true]
        ])->orderByDesc('id')->first();

        if($from) {
            $dateFrom = $from->event_date;
        } else {
            $dateFrom = $globalDateFrom;
        }

        return [Carbon::create($dateFrom), Carbon::create($dateTo)];
    }

    public function getPeriodFuelRemains($responsibleId, $fuelTankId, $objectId, $dateFrom, $dateTo)
    {
        $tankRecievedEvent = FuelTankTransferHistory::where([
                ['responsible_id', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                ['object_id', $objectId],
                ['tank_moving_confirmation', 1],
                ['event_date', $dateFrom]
        ])->first();

        if($tankRecievedEvent) {
            $fuelLevelPeriodStart =  $tankRecievedEvent->fuel_level;
        } else {
            $lastPreviousPeriodFuelRemain = FuelTankTransferHistory::where([
                ['responsible_id', $responsibleId],
                ['fuel_tank_id', $fuelTankId],
                ['object_id', $objectId],
                ['event_date', '<' ,$dateFrom]
            ])->orderByDesc('id')->first();
            if($lastPreviousPeriodFuelRemain) {
                $fuelLevelPeriodStart =  $lastPreviousPeriodFuelRemain->fuel_level;
            } else {
                $fuelLevelPeriodStart = 0;
            } 
        }

        $fuelPeriodLastTransaction = $fuelPeriodPreviousTransaction = FuelTankTransferHistory::where([
            ['responsible_id', $responsibleId],
            ['fuel_tank_id', $fuelTankId],
            ['object_id', $objectId],
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

        return [$fuelLevelPeriodStart, $fuelLevelPeriodFinish];
    }

    public function getFilteredArray($filterData, $filterKey)
    {
        if(!str_contains(json_encode($filterData), $filterKey))
        return [];

        $filterData = (array)$filterData;
        $resultArr = [];
        $i=0;
        array_walk_recursive($filterData, function($value, $key) use(&$resultArr, &$i, &$filterKey) {
            if($value === $filterKey) {
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
