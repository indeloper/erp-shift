<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Requests\HumanAccountingReportRequests\WorkTimeReportGenerateRequest;
use App\Services\HumanResources\Reports\WorkingTimeReportExport;
use App\Services\HumanResources\TimecardService;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Integer;
use App\Http\Requests\ReportGroupRequests\{ReportGroupDestroyRequest,
    ReportGroupStoreRequest,
    ReportGroupUpdateRequest};
use App\Services\AuthorizeService;
use App\Traits\AdditionalFunctions;
use App\Models\HumanResources\{JobCategory, ReportGroup, TariffRates, Timecard, TimecardDay, TimecardRecord};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AdditionalFunctions;

    /** @var TimecardService */
    protected $timecardService;

    public function __construct()
    {
        parent::__construct();
        $this->timecardService = new TimecardService();
    }

    public function detailedReport(Request $request)
    {
        return view('human_resources.reports.detailed_monthly_report', [
            'data' => $this->timecardService->collectDetailedData($request->user_id, $request->month),
            'projects' => Project::get()->map(function ($item) {
                return ['code' => $item->id, 'name' => $item->name_with_object];
            })->toArray(),
        ]);
    }

    public function getDetailedData(Request $request)
    {
        return ['data' => $this->timecardService->collectDetailedData($request->user_id, $request->month)];
    }

    public function summaryReport(Request $request)
    {
        return view('human_resources.reports.summary_report');
    }

    /**
     * @param Request $request
     * request should contain day and project_id fields
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dailyReport(Request $request)
    {
        $isUserHasWorkers = auth()->user()->timeResponsibleProjects()->where(function($rel) {
            $rel->whereHas('users')->orWhereHas('brigades');
        })->exists();

        if (!$isUserHasWorkers and auth()->id() != 1) {
            return back();
        }
        $workers = [];
        $task_id = null;
        if ($request->project_id) {
            $workers = TimecardDay::filter($request)
                ->select('id', 'timecard_id', 'user_id')
                ->get();

            $workers->each(function($worker) {$worker->append('user_name');});

            $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
            $task = Project::findOrFail($request->project_id)->tasks()->where('status', 41)->where('is_solved', 0)->whereDate('created_at', $date)->first();
            $task_id = $task->id ?? null;
        }
        return view('human_resources.reports.daily_report', [
            'data' => [
                'workers' => $workers,
                'tariff_manual' => TariffRates::select('id', 'name', 'type')->get(),
                'task_id' => $task_id,
            ],
            'projects' => Project::get()->map(function ($item) {
                return ['code' => $item->id, 'name' => $item->name_with_object];
            })->toArray()
        ]);
    }

    public function getDailyData(Request $request)
    {
        $data = $this->timecardService->collectDailyTimecards($request->day, $request->project_id);

        return ['data' => $data];
    }

    /**
     * Function can generate report for two filters:
     * project and time period
     * @param WorkTimeReportGenerateRequest $request
     */
    public function generateWorkTimeReport(Request $request)
    {
        // Right now I don't have all data for Excel report generation,
        // here payload only

        $timecards = Timecard::reportFilter($request);
        $timecards->whereHas('user', function($user_q) {
            $user_q->whereHas('reportGroup');
        });

        $dateStringLength = mb_strlen($request->date);
        if ($dateStringLength === 7) {
            // YYYY-MM
            // Month
            [$year, $month] = explode('-', $request->date);
            $start = now()->month($month)->startOfMonth()->format('Y-m-d');
            $end = now()->month($month)->endOfMonth()->format('Y-m-d');
        } elseif ($dateStringLength === 21) {
            // YYYY-MM-DD|YYYY-MM-DD
            // Period
            [$start, $end] = explode('|', $request->date);
        } elseif ($dateStringLength === 0) {
            return abort(404);
        }

        $data = collect(
            $timecards->get()->map(function ($timecard) use ($start, $end, $request) {
                $user = $timecard->user;
                $project_id = $request->project_id;
                $collection = collect();
                $collection->user = $user->load(['reportGroup', 'jobCategory.tariffs']);
                $collection->ktu = $timecard->ktu;
                $collection->records = $timecard->records()
                    ->when($project_id, function ($query, $project_id) {
                        return $query->where(function ($q) use ($project_id) {
                            $q->where('project_id', $project_id)->orWhereNull('project_id');
                        });
                    })
                    ->whereIn('type', [TimecardRecord::TYPES_ENG['working hours'], TimecardRecord::TYPES_ENG['deals']])
                    ->get()
                    ->whereBetween('date', [$start, $end]);
                $collection->additions = $timecard->additions()
                    ->when($project_id, function ($query, $project_id) {
                        return $query->where(function ($q) use ($project_id) {
                            $q->where('project_id', $project_id)->orWhereNull('project_id');
                        });
                    })
                    ->get();
                return $collection;
            })
        );
        if (!count($data)) {
            return abort(404);
        }
        $report = new WorkingTimeReportExport($data);

        return $report->export();
    }
}
