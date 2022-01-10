<?php


namespace App\Services\HumanResources;


use App\Models\HumanResources\Timecard;
use App\Models\HumanResources\TimecardRecord;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class TimecardService
{
    public function collectDailyTimecards($day, $project_id)
    {
        $day = $day ?? Carbon::now()->day;
        $project = $project_id ? Project::find($project_id) : auth()->user()->timeResponsibleProjects()->first();

        $data = [];
        if ($project) {
            foreach ($project->users as $user) {
                $timecard = Timecard::where('user_id', $user->id)->first();
                if ($timecard) {
                    $data[$user->id] = $timecard->getDailyRecords($day);
                }
            }
        }
        return $data;
    }

    public function collectSummaryForCustomPeriod($date_from, $date_to, $user_id = null, $project_id = null)
    {
        $date_to = Carbon::parse($date_to);
        $date_from = Carbon::parse($date_from);

        $records = TimecardRecord::query()
            ->leftJoin('timecard_days', 'timecard_day_id', 'timecard_days.id')
            ->leftJoin('timecards', 'timecard_id', 'timecards.id')
            ->whereRaw("cast(concat(timecards.year, '-', timecards.month, '-', timecard_days.day) as datetime) between cast('{$date_from}' as datetime) and cast('{$date_to}' as datetime)");

        $deals = with(clone $records)->deals()->groupBy('timecards.user_id', 'tariff_id')
            ->selectRaw('timecard_records.*, timecards.user_id as worker_id, timecards.user_id, sum(length * amount) as sum')
            ->get()->groupBy('worker_id');

        $hours = with(clone $records)->workingHours()->groupBy('timecards.user_id', 'tariff_id')
            ->selectRaw('timecard_records.*, timecards.user_id as worker_id, timecards.user_id, sum(amount) as sum')
            ->get()->groupBy('worker_id');

        $reports = null;

        foreach ($deals->keys()->union($hours->keys()) as $user_id) {
            $user_hours = $hours->get($user_id, collect())->keyBy('tariff_id');
            $user_deals = $deals->get($user_id, collect())->keyBy('tariff_id');
            $user_report = $user_hours->union($user_deals);
            $user_report[0] = ['sum' => $user_report->sum('sum')];
            $user = User::find($user_id);
            $user->timecards = $user_report;
            $reports[$user->id] = $user;
        }


        return ['users' => $reports];
    }

    public function collectDetailedData($user_id = null, $month = null)
    {
        if ($user_id and $month) {
            try {
                $parsed_month = Carbon::parse($month)->month;
                $parsed_year = Carbon::parse($month)->year;
            } catch (\Throwable $e) {
                return abort(404);
            }
            $timecard = Timecard::where('month', $parsed_month)->where('year', $parsed_year)->where('user_id', $user_id)->first();
            if (!$timecard) {
                $user = User::findOrFail($user_id);
                $timecard = $this->fixUserTimecard($user, $parsed_month, $parsed_year);
                $timecard->refresh();
            };

            $timecard->append('detailed_data', 'days_id', 'summarized_data');
            $timecard->loadMissing('bonuses', 'fines', 'compensations');
        }

        return $timecard ?? [];
    }

    public function fixUserTimecard($user, $month = null, $year = null)
    {
        $parsed_month = $month ?? Carbon::now()->month;
        $parsed_year = $year ?? Carbon::now()->year;
        if (!$user->timecards()->where('month', $parsed_month)->where('year', $parsed_year)->exists()) {
            return Timecard::create([
                'user_id' => $user->id,
                'author_id' => auth()->id(),
                'month' => $parsed_month,
                'year' => $parsed_year,
            ]);
        }
    }
}
