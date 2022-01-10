<?php

namespace App\Models\HumanResources;

use App\Models\{Project, Task, User};
use App\Traits\{HasAuthor, Logable};
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOneThrough};
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimecardDay extends Model
{
    use SoftDeletes, Logable, HasAuthor;

    protected $fillable = ['timecard_id', 'user_id', 'day', 'is_opened', 'completed'];

    // Scopes
    /**
     * Return timecard days filter.
     * Searching by project and date (d.m.Y format)
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeFilter(Builder $query, Request $request)
    {
        $projectId = $request->project_id;
        $date = Carbon::createFromFormat('d.m.Y', $request->date ?? now()->format('d.m.Y'));
        $month = $date->month;
        $day = $date->day;

        if ($projectId) {
            $projectWorkers = Project::findOrFail($projectId)->allUsers()->pluck('id')->toArray();
            $query->where('day', $day)
                ->whereHas('timecard', function ($query) use ($month) {
                    $query->where('timecards.month', $month);
                })->whereHas('user', function ($subquery) use ($projectWorkers) {
                    $subquery->whereIn('users.id', $projectWorkers);
                })->with(['deals' => function($deal_q) {
                    $deal_q->select('id', 'amount', 'tariff_id', 'type', 'timecard_day_id', 'length');
                }, 'workingHours' => function($hour_q) {
                    $hour_q->select('id', 'amount', 'tariff_id', 'type', 'timecard_day_id');
                }, 'timePeriods' => function($time_q) {
                    $time_q->select('id', 'type', 'timecard_day_id', 'project_id', 'start', 'end', 'commentary');
                }]);
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    // Getters

    // Relations
    /**
     * Relation to parent timecard
     * @return BelongsTo
     */
    public function timecard(): BelongsTo
    {
        return $this->belongsTo(Timecard::class, 'timecard_id', 'id');
    }

    /**
     * Common relation for all timecard records:
     * Bonuses, Compensations, Fines
     * @return mixed
     */
    public function records()
    {
        return $this->hasMany(TimecardRecord::class);
    }

    /**
     * Relation for all timecard deals
     * @return mixed
     */
    public function deals()
    {
        return $this->records()->where('type', TimecardRecord::TYPES_ENG['deals']);
    }

    /**
     * Relation for all timecard workingHours
     * @return mixed
     */
    public function workingHours()
    {
        return $this->records()->where('type', TimecardRecord::TYPES_ENG['working hours']);
    }

    /**
     * Relation for all timecard projects
     * @return mixed
     */
    public function timePeriods()
    {
        return $this->records()->where('type', TimecardRecord::TYPES_ENG['time periods']);
    }

    /**
     * Function for timecard day user
     * @return HasOneThrough
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Timecard::class, 'id', 'id', 'timecard_id', 'user_id');
    }

    public function getUserNameAttribute()
    {
        return $this->user->full_name;
    }

    // Methods
    /**
     * Function call delete function on each deleted timecard day record
     * @param $deleted_record_ids
     * @return void
     */
    public function deleteRecords($deleted_record_ids): void
    {
        foreach ($deleted_record_ids as $record_id) {
            $this->deleteRecord($record_id);
        }
    }

    /**
     * Function delete timecard day record
     * and trigger model observer logic
     * @param $record_id
     * @return void
     */
    public function deleteRecord($record_id): void
    {
        $this->records()->findOrFail($record_id)->delete();
    }

    /**
     * Function create or update timecard day record
     * for given type
     * @param string $recordType
     * @param array $records
     * @return mixed
     */
    public function updateRecords(string $recordType, array $records)
    {
        $updated_periods = collect();
        foreach ($records as $record) {
            $updated_periods->push((isset($record['id']) and $record['id'] !== -1)
                ? $this->updateExistedRecord($record)
                : $this->createNewRecord(TimecardRecord::TYPES_ENG[$recordType], $record));
        }
        return $updated_periods;
    }

    /**
     * Function finds timecard day record and update it
     * @param array $record
     * @return mixed
     */
    public function updateExistedRecord(array $record)
    {
        $period = $this->records()->findOrFail($record['id']);
        $period->update([
            'start' => $record['start'] ?? null,
            'end' => $record['end'] ?? null,
            'project_id' => $record['project_id'] ?? null,
            'commentary' => $record['commentary'] ?? null,
            'tariff_id' => $record['tariff_id'] ?? null,
            'length' => $record['length'] ?? null,
            'amount' => $record['amount'] ?? null,
        ]);
        $period->refresh();
        return $period;
    }

    /**
     * Function create new record for timecard
     * @param int $type
     * @param array $record
     * @return mixed
     */
    public function createNewRecord(int $type, array $record)
    {
        return $this->records()->create([
            'type' => $type,
            'start' => $record['start'] ?? null,
            'end' => $record['end'] ?? null,
            'project_id' => $record['project_id'] ?? null,
            'commentary' => $record['commentary'] ?? null,
            'tariff_id' => $record['tariff_id'] ?? null,
            'length' => $record['length'] ?? null,
            'amount' => $record['amount'] ?? null,
        ]);
    }

    /**
     * Function check if we have project users with
     * timecard days without time periods.
     * If we doesn't have them, task will be solved
     * @param $task_id
     * @return void
     */
    public function checkTask40($task_id): void
    {
        $task = Task::where('status', 40)->findOrFail($task_id);
        $projectWorkers = $task->project->allUsers()->pluck('id')->toArray();
        $date = Carbon::parse($task->created_at);
        $month = $date->month;
        $day = $date->day;
        $timecardDaysWithoutTimePeriods = self::where('day', $day)
            ->whereHas('timecard', function ($query) use ($month) {
                $query->where('timecards.month', $month);
            })->whereHas('user', function ($subquery) use ($projectWorkers) {
                $subquery->whereIn('users.id', $projectWorkers);
            })->doesntHave('timePeriods');

        if(! $timecardDaysWithoutTimePeriods->exists()) {
            $task->solve_n_notify();
        }
    }
}
