<?php

namespace App\Models\HumanResources;

use App\Models\{Project, User};
use App\Traits\Logable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class Timecard extends Model
{
    use SoftDeletes, Logable;

    protected $fillable = ['user_id', 'author_id', 'year', 'month', 'ktu', 'is_opened'];

    protected $appends = ['tariff_manual'];

    // Scopes
    /**
     * Return timecard filter.
     * Searching by project and date (month or period in d.m.Y|d.m.Y format)
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeReportFilter(Builder $query, Request $request): Builder
    {
        $projectId = $request->project_id;
        $userId = $request->user_id;
        if ($projectId) {
            $query->whereHas('records', function ($query) use ($projectId) {
                $query->where('timecard_records.project_id', $projectId);
            })->orWhereHas('user', function($user_q) use ($projectId) {
                $user_q->whereHas('appointments', function($app_q) use ($projectId) {
                    $app_q->where('project_id', $projectId);
                });
            });
        }

        if ($userId) {
            $query->where('timecards.user_id', $userId);
        }

        $dateStringLength = mb_strlen($request->date);
        if ($dateStringLength === 7) {
            // YYYY-MM
            // search by month
            [$year, $month] = explode('-', $request->date);
            $query->where('year', $year)->where('month', $month);
        } elseif ($dateStringLength === 21) {
            // YYYY-MM-DD|YYYY-MM-DD
            // search by period
            [$date_from, $date_to] = explode('|', $request->date);
            $query->join('timecard_days', 'timecards.id', 'timecard_days.timecard_id')
                ->join('timecard_records', 'timecard_days.id', 'timecard_records.timecard_day_id')
                ->whereRaw("cast(concat(timecards.year, '-', timecards.month, '-', timecard_days.day) as date) between cast('{$date_from}' as date) and cast('{$date_to}' as date)")
                ->distinct()
                ->select('timecards.*');
        }

        return $query;
    }

    // Getters

    public function getTariffManualAttribute()
    {
        return TariffRates::select('id', 'name', 'type')->get();
    }

    // Relations
    /**
     * Relation for timecard user
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relation for timecard author
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Common relation for all timecard additions:
     * Bonuses, Compensations, Fines
     * @return mixed
     */
    public function additions()
    {
        return $this->hasMany(TimecardAddition::class);
    }

    /**
     * Relation for all timecard bonuses
     * @return mixed
     */
    public function bonuses()
    {
        return $this->additions()->where('type', TimecardAddition::TYPES_ENG['bonus']);
    }

    /**
     * Relation for all timecard compensations
     * @return mixed
     */
    public function compensations()
    {
        return $this->additions()->where('type', TimecardAddition::TYPES_ENG['compensation']);
    }

    /**
     * Relation for all timecard fines
     * @return mixed
     */
    public function fines()
    {
        return $this->additions()->where('type', TimecardAddition::TYPES_ENG['fine']);
    }

    /**
     * Relation for all timecard days
     * @return mixed
     */
    public function days()
    {
        return $this->hasMany(TimecardDay::class);
    }

    public function getDaysIdAttribute()
    {
        return $this->days()->select('id', 'day')->get()->keyBy('day');
    }

    /**
     * Common relation for all timecard records:
     * Bonuses, Compensations, Fines
     * @return mixed
     */
    public function records()
    {
        return $this->hasManyThrough(TimecardRecord::class, TimecardDay::class);
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

    // Methods
    /**
     * Function call delete function on each deleted timecard addition
     * @param $deleted_addition_ids
     * @return void
     */
    public function deleteAdditions($deleted_addition_ids): void
    {
        foreach ($deleted_addition_ids as $addition_id) {
            $this->deleteAddition($addition_id);
        }
    }

    /**
     * Function delete timecard addition
     * and trigger model observer logic
     * @param $addition_id
     * @return void
     */
    public function deleteAddition($addition_id): void
    {
        $this->additions()->findOrFail($addition_id)->delete();
    }

    /**
     * Function create or update timecard additions
     * for given type
     * @param string $additionType
     * @param array $additions
     * @return \Illuminate\Support\Collection
     */
    public function updateAdditions(string $additionType, array $additions)
    {
        $updated_additions = collect();
        foreach ($additions as $addition) {
            $updated_additions->push( (isset($addition['id']) and $addition['id'] !== -1)
                ? $this->updateExistedAddition($addition)
                : $this->createNewAddition(TimecardAddition::TYPES_ENG[$additionType], $addition));
        }

        return $updated_additions;
    }

    /**
     * Function finds timecard addition and update it
     * @param array $addition
     * @return mixed
     */
    public function updateExistedAddition(array $addition)
    {
        $updated_addition = $this->additions()->findOrFail($addition['id']);
        $updated_addition->update([
            'name' => $addition['name'],
            'amount' => round($addition['amount'], 2),
            'prolonged' => $addition['prolonged'] ?? 0,
            'project_id' => $addition['project_id'] ?? null,
        ]);
        $updated_addition->refresh();
        return $updated_addition;
    }

    /**
     * Function create new addition for timecard
     * @param int $type
     * @param array $addition
     * @return mixed
     */
    public function createNewAddition(int $type, array $addition)
    {
        $created_addition =  $this->additions()->create([
            'type' => $type,
            'name' => $addition['name'],
            'amount' => round($addition['amount'], 2),
            'prolonged' => $addition['prolonged'] ?? 0,
            'project_id' => $addition['project_id'] ?? null,
        ]);
        $created_addition->refresh();

        return $created_addition;
    }


    /**
     * Function create timecard days for all days in month
     */
    public function createMonthDays(): void
    {
        $daysInMonth = range(1, now()->daysInMonth);
        foreach ($daysInMonth as $day) {
            $this->days()->create([
                'day' => $day,
                'user_id' => auth()->id() ?? 1
            ]);
        }
    }

    public function getSummarizedDataAttribute()
    {
        $this->load('user');
        $deals = $this->deals()->groupBy('tariff_id')->selectRaw('tariff_id, type, sum(length * amount) as sum')->get()->keyBy('tariff_id');
        $hours = $this->workingHours()->groupBy('tariff_id')->selectRaw('tariff_id, type, sum(amount) as sum')->get()->keyBy('tariff_id');
        $report = $hours->union($deals);
        $report[0] = ['sum' => $report->sum('sum')];

        return $report;
    }

    public function getDetailedDataAttribute($day = null)
    {
        $this->load('user');
        $records = $this->records();
        if ($day) {
            $records->where('timecard_days.day', $day);
        }
        $records = $records->selectRaw('timecard_records.*, timecard_days.day')->get()->groupBy('day');
        return $records;
    }

    public function updateDealsGroup(array $fields): void
    {
        $this->deals()
            ->where('tariff_id', $fields['old_tariff'])->where('length', $fields['old_length'])
            ->update([
                'tariff_id' => $fields['new_tariff'] ?? $fields['old_tariff'],
                'length' => $fields['new_length'] ?? $fields['old_length'],
            ]);
    }

    public function getDailyRecords($day)
    {
        $this->load('user');

        $timePeriods = $this->timePeriods()->where('timecard_days.day', $day)->with('project')->get();

        $records = $this->records()
            ->whereIn('type', [TimecardRecord::TYPES_ENG['working hours'], TimecardRecord::TYPES_ENG['deals']])
            ->where('timecard_days.day', $day)
            ->get();

        return $records->union(['0' => ['sum' => $records->sum('sum')], 'times' => $timePeriods]);
    }
}
