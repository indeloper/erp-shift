<?php

namespace App\Http\Requests\TimecardRequests;

use App\Models\HumanResources\{TimecardDay, TimecardRecord};
use App\Models\{Project, Task};
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class WorkingHoursTaskSolveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return (bool) auth()->user()->hasPermission('human_resources_timecard_fill');
    }

    public function withValidator($validator)
    {
        $task = Task::findOrFail($this->get('task_id'));
        $projectWorkers = Project::findOrFail($this->get('project_id'))
            ->allUsers()->pluck('id')->toArray();
        $date = Carbon::parse($task->created_at);
        $daysWithoutCompletedTimePeriods = TimecardDay::where('day', $date->day)
            ->whereHas('timecard', function ($query) use ($date) {
                $query->where('timecards.month', $date->month)->where('timecards.year', $date->year);
            })->whereHas('user', function ($subquery) use ($projectWorkers) {
                $subquery->whereIn('users.id', $projectWorkers);
            })->whereHas('timePeriods', function ($q) {
                $q->where('end', '=', '')
                    ->orWhereNull('end');
            })->with('timePeriods')->count();
        if ($daysWithoutCompletedTimePeriods) {
            $validator->errors()->add('not_completed', 'Необходимо указать всем сотрудникам временные периоды с началом и концом');
            throw new ValidationException($validator);
        }

        if (! $this->checkWorkingHours($projectWorkers, $date)) {
            $validator->errors()->add(
                'must_fill_hours',
                'Каждый сотрудник, имеющий временной промежуток, обязательно должен иметь тарифы'
            );
            throw new ValidationException($validator);
        }

        // sorry, but I need to pass validator here
        $this->checkTimePeriodsAndWorkingHours($projectWorkers, $date, $validator);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'task_id' => ['required', 'exists:tasks,id'],
        ];
    }

    private function checkWorkingHours(array $userIds, Carbon $date): bool
    {
        $dayIds = TimecardDay::where('day', $date->day)
            ->whereHas('timecard', function ($query) use ($date) {
                $query->where('timecards.month', $date->month);
            })->whereHas('user', function ($subquery) use ($userIds) {
                $subquery->whereIn('users.id', $userIds);
            })->pluck('id');

        return TimecardRecord::where('type', TimecardRecord::TYPES_ENG['working hours'])
            ->whereIn('timecard_day_id', $dayIds)->count() > 0;
    }

    private function checkTimePeriodsAndWorkingHours(array $userIds, Carbon $date, $validator)
    {
        $dayIds = TimecardDay::where('day', $date->day)
            ->whereHas('timecard', function ($query) use ($date) {
                $query->where('timecards.month', $date->month);
            })->whereHas('user', function ($subquery) use ($userIds) {
                $subquery->whereIn('users.id', $userIds);
            })->pluck('id');
        $grouppedTimeRecords = TimecardRecord::where('type', TimecardRecord::TYPES_ENG['time periods'])
            ->whereIn('timecard_day_id', $dayIds)->get()->groupBy('timecard_day_id', 'project_id');
        $timePeriodMinutesPerTimecard = [];
        foreach ($grouppedTimeRecords as $key => $group) {
            foreach ($group as $timePeriod) {
                if (! array_key_exists($key, $timePeriodMinutesPerTimecard)) {
                    $timePeriodMinutesPerTimecard[$key] = [];
                }
                if (! array_key_exists($timePeriod->project_id, $timePeriodMinutesPerTimecard[$key])) {
                    $timePeriodMinutesPerTimecard[$key][$timePeriod->project_id] = $timePeriod->getTimePeriodHours();
                } else {
                    $timePeriodMinutesPerTimecard[$key][$timePeriod->project_id] += $timePeriod->getTimePeriodHours();
                }
            }
        }
        $grouppedWorkingHours = TimecardRecord::where('type', TimecardRecord::TYPES_ENG['working hours'])
            ->whereIn('timecard_day_id', $dayIds)->get()->groupBy('timecard_day_id', 'project_id');
        $workingMinutesPerTimecard = [];
        foreach ($grouppedWorkingHours as $key => $group) {
            foreach ($group as $workingHour) {
                if (! array_key_exists($key, $workingMinutesPerTimecard)) {
                    $workingMinutesPerTimecard[$key] = [];
                }
                if (! array_key_exists($workingHour->project_id, $workingMinutesPerTimecard[$key])) {
                    $workingMinutesPerTimecard[$key][$workingHour->project_id] = $workingHour->amount * 60;
                } else {
                    $workingMinutesPerTimecard[$key][$workingHour->project_id] += $workingHour->amount * 60;
                }
            }
        }

        foreach ($timePeriodMinutesPerTimecard as $timecardId => $minutesPerProject) {
            foreach ($minutesPerProject as $project => $minutes) {
                if (! array_key_exists($project, $workingMinutesPerTimecard[$timecardId])) {
                    $validator->errors()->add(
                        'missing_working_hours',
                        'У каждого временного промежутка, имеющего привязку к проекту, должен быть тариф на этом же проекте'
                    );
                    throw new ValidationException($validator);
                }
                if ($this->get('approve') !== 1) {
                    if ($minutes < $workingMinutesPerTimecard[$timecardId][$project]) {
                        $validator->errors()->add(
                            'too_much',
                            'Сотрудник не может иметь рабочих часов на проекте больше, чем он проработал на нём за день. Если вы уверены в правильности данных, вы можете их подтвердить'
                        );
                        throw new ValidationException($validator);
                    }
                }
            }
        }
    }
}
