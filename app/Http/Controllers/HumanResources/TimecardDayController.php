<?php

namespace App\Http\Controllers\HumanResources;

use App\Models\HumanResources\TariffRates;
use App\Models\HumanResources\TimecardDay;
use App\Models\HumanResources\TimecardRecord;
use App\Models\Project;
use App\Models\Task;
use App\Services\HumanResources\TimecardService;
use App\Traits\NotificationGenerator;
use App\Http\Requests\TimecardRequests\{TimecardDayDealsGroupDestoyRequest,
    TimecardDayDealsUpdateRequest,
    TimecardDayTimePeriodsUpdateRequest,
    TimecardDayWorkingHoursUpdateRequest,
    TimecardDealsGroupUpdateRequest,
    UpdateDayDealsGroup,
    WorkingHoursTaskSolveRequest};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimecardDayController extends Controller
{
    use NotificationGenerator;

    /**
     * Function update timecard day time periods
     * @param TimecardDayTimePeriodsUpdateRequest $request
     * @param TimecardDay $timecardDay
     * @return array
     */
    public function updateTimePeriods(TimecardDayTimePeriodsUpdateRequest $request, TimecardDay $timecardDay): array
    {
        DB::beginTransaction();
        $data = '';
        if (! empty($request->deleted_addition_ids)) {
            $timecardDay->deleteRecords($request->deleted_addition_ids);
        }
        if (! empty($request->periods)) {
            $data = $timecardDay->updateRecords('time periods', $request->periods ?? []);
        }
        $timecardDay->generateAction('time periods list update');

        if ($request->has('task_id')) {
            $timecardDay->checkTask40($request->task_id);
        }

        DB::commit();

        return [
            'result' => 'success',
            'data' => $data,
        ];
    }

    /**
     * Function update timecard deals
     * @param TimecardDayDealsUpdateRequest $request
     * @param TimecardDay $timecardDay
     * @return array
     */
    public function updateDeals(TimecardDayDealsUpdateRequest $request, TimecardDay $timecardDay): array
    {
        DB::beginTransaction();
        $data = '';
        if (! empty($request->deleted_addition_ids)) {
            $timecardDay->deleteRecords($request->deleted_addition_ids);
        }
        if (! empty($request->deals)) {
            $data = $timecardDay->updateRecords('deals', $request->deals ?? []);
        }
        $timecardDay->generateAction('deals list update');

        DB::commit();

        return [
            'result' => 'success',
            'data' => $data,
        ];
    }

    /**
     * Function update timecard working hours
     * @param TimecardDayWorkingHoursUpdateRequest $request
     * @param TimecardDay $timecardDay
     * @return array
     */
    public function updateWorkingHours(TimecardDayWorkingHoursUpdateRequest $request, TimecardDay $timecardDay): array
    {
        DB::beginTransaction();
        $data = '';
        if (! empty($request->deleted_addition_ids)) {
            $timecardDay->deleteRecords($request->deleted_addition_ids);
        }
        if (! empty($request->working_hours)) {
            $data = $timecardDay->updateRecords('working hours', $request->working_hours ?? []);
        }
        $timecardDay->generateAction('working hours list update');

        DB::commit();

        return [
            'result' => 'success',
            'data' => $data,
        ];
    }

    /***
     * gets day, project and parameters to update
     * @param $request
     */
    public function updateDayDealsGroup(UpdateDayDealsGroup $request)
    {
        DB::beginTransaction();
        $date = Carbon::parse($request->day);
        $projectWorkersId = Project::findOrFail($request->project_id)->allUsers()->pluck('id')->toArray();
        $days_to_update = TimecardDay::where('day', $date->day)
            ->whereHas('timecard', function($card) use ($date, $projectWorkersId) {
                $card->where('month', $date->month)->where('year', $date->year)->whereIn('user_id', $projectWorkersId);
            })
            ->whereHas('deals', function($deal) use ($request) {
                $deal->where('tariff_id', $request->old_tariff)->where('length', $request->old_length);
            })->with('deals')->get();

        foreach ($days_to_update as $day) {
            $day->deals()->where('tariff_id', $request->old_tariff)->where('length', $request->old_length)
                ->update([
                    'tariff_id' => $request->new_tariff ?? $request->old_tariff,
                    'length' => $request->new_length ?? $request->old_length,
                ]);

            $day->timecard->generateAction('deals group update');
        }
        DB::commit();

        return [
            'result' => 'success',
        ];
    }

    public function destroyDayDealsGroup(TimecardDayDealsGroupDestoyRequest $request)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($request->project_id);
        $date = Carbon::parse($request->day);
        $users_id = $project->allUsers()->pluck('id');
        $deals = TimecardRecord::query()
            ->where('tariff_id', $request->tariff_id)
            ->where('length', $request->length)
            ->whereHas('timecard', function($timecard_q) use ($users_id, $date) {
                $timecard_q
                    ->whereIn('timecards.user_id', $users_id)
                    ->where('month', $date->month)
                    ->where('year', $date->year);
            })
            ->whereHas('timecardDay', function($day) use ($date) {
                $day->where('day', $date->day);
            })
            ->get()->each->delete();

        DB::commit();
    }

    /**
     * Function can find timecard for parameters given via request
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $workers = TimecardDay::filter($request)
            ->select('id', 'timecard_id', 'user_id')
            ->get();
        $task_id = null;
        if ($request->project_id) {
            $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
            $task = Project::findOrFail($request->project_id)->tasks()->where('status', 41)->where('is_solved', 0)->whereDate('created_at', $date)->first();
            $task_id = $task->id ?? null;
        }
        $workers->each(function($worker) {$worker->append('user_name');});
        return [
            'data' => [
                'workers' => $workers,
                'tariff_manual' => TariffRates::select('id', 'name', 'type')->get(),
                'task_id' => $task_id,
            ]
        ];
    }

    /**
     * Function return appearance control task view
     * @param Task $task
     * @return mixed
     */
    public function appearanceTask(Task $task)
    {
        $time_resp = $task->responsible_user;
        (new TimecardDay())->checkTask40($task->id);
        $task->refresh();
        if (!$task->is_unsolved()) {
            return back();
        }
        $project = $task->project;
        $projectWorkers = $project->allUsers();
        if (!$projectWorkers) {$task->solve(); return back();}

        foreach ($projectWorkers as $worker) {
            (new TimecardService())->fixUserTimecard($worker);
        }

        $projectWorkers = $projectWorkers->pluck('id')->toArray();

        $date = Carbon::parse($task->created_at);
        $daysWithoutTimePeriods = TimecardDay::where('day', $date->day)
            ->whereHas('timecard', function ($query) use ($date) {
                $query->where('timecards.month', $date->month);
            })->whereHas('user', function ($subquery) use ($projectWorkers) {
                $subquery->whereIn('users.id', $projectWorkers);
            })->doesntHave('timePeriods')
            ->with('user')->get();

        $users = [];
        foreach ($daysWithoutTimePeriods as $day) {
            $users[] = [
                'id' => $day->user->id,
                'long_full_name' => $day->user->long_full_name,
                'group_name' => $day->user->group_name,
                'company_name' => $day->user->company_name,
                'timecard_day_id' => $day->id,
                'working_status' => 1,
                'period' => [
                    'project_id' => $task->project_id,
                ],
            ];
        }

        return view('tasks.human_resources.40', [
            'task' => $task,
            'users' => $users,
        ]);
    }

    /**
     * Function return redirect to timecard day for some project
     * @param Task $task
     * @return mixed
     */
    public function workingTimeTask(Task $task)
    {
        $task->is_seen = 1;
        $task->save();

        return redirect(route('human_resources.report.daily_report', [
            'project_id' => $task->project_id,
            'date' => Carbon::parse($task->created_at)->isoFormat('DD.MM.YYYY'),
        ]));
    }

    /**
     * Function check if task can be solved
     * @param WorkingHoursTaskSolveRequest $request
     * @return array
     */
    public function solveWorkingTimeTask(WorkingHoursTaskSolveRequest $request): array
    {
        $task = Task::find($request->get('task_id'));
        $task->solve_n_notify();
        if ($request->get('approve') === 1) {
            $this->createWorkTimeControlTaskPossibleExceedanceNotificationFor($task);
        }

        return ['result' => 'success'];
    }
}
