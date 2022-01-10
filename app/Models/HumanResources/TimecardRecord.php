<?php

namespace App\Models\HumanResources;

use App\Models\Project;
use Carbon\Carbon;
use App\Traits\{HasAuthor, Logable};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimecardRecord extends Model
{
    use SoftDeletes, Logable, HasAuthor;

    protected $fillable = [
        'timecard_day_id',
        'user_id',
        'type',
        'tariff_id',
        'project_id',
        'length',
        'amount',
        'start',
        'end',
        'commentary'
    ];

    protected $appends = ['tariff_name'];

    public const TYPES = [
        1 => 'Временной промежуток',
        2 => 'Рабочие часы',
        3 => 'Сделка',
    ];

    public const TYPES_ENG = [
        'time periods' => 1,
        'working hours' => 2,
        'deals' => 3,
    ];

    public const LONG_COMMENTARIES = [
        'Б' => 'Больничный',
        'З' => 'Отпуск за свой счёт',
        'Н' => 'Отсутствие по невыясненной причине',
        'О' => 'Отпуск',
        'П' => 'Прогул',
        'У' => 'Учебный отпуск',
    ];

    public const COMMENTARIES = [
        1 => 'Б',
        2 => 'З',
        3 => 'Н',
        4 => 'О',
        5 => 'П',
        6 => 'У',
    ];

    private const ENTITY_TYPE_PROPERTIES = [
        'time periods' => [
            // can be two types - type period on some project or custom time period with name
            'start' => 'required', // always required
            'end' => 'required', // we can't store end without start
            'project_id' => 'sometimes', // can be nullable if we store custom time period without project
            'commentary' => 'sometimes', // can be nullable if we store project time period, can hold custom name or something from self::COMMENTARIES
        ],
        'working hours' => [
            // used to store working hours info like tariff rate and amount in hours
            'tariff_id' => 'required', // always required
            'amount' => 'required', // always required, displays length of time period in hours (only integers, no double)
        ],
        'deals' => [
            // used to store deals info like tariff rate, tongue length and amount
            'tariff_id' => 'required', // always required
            'length' => 'required', // always required, displays tongue length that was used in deal (double)
            'amount' => 'required', // always required, displays deal amount (integer)
        ],
        // all have timecard_day_id
    ];

    // Scopes

    public function scopeDeals(Builder $q)
    {
        return $q->where('type', self::TYPES_ENG['deals']);
    }

    public function scopeTimePeriods(Builder $q)
    {
        return $q->where('type', self::TYPES_ENG['time periods']);
    }

    public function scopeWorkingHours(Builder $q)
    {
        return $q->where('type', self::TYPES_ENG['working hours']);
    }

    public function scopeFilterByDates(Builder $q, $date_from, $date_to)
    {
        $date_from = Carbon::parse($date_from);
        $date_to = Carbon::parse($date_to);

        $q
            ->where(function($q) use ($date_from, $date_to) {
                return $q
//                    ->orWhereHas('timecard', function ($card) use ($date_from) {
//                        return $card->where('month', $date_from->month)->whereYear('timecards.created_at', $date_from->year);
//                    })
                    ->whereHas('timecard', function ($card) use ($date_from, $date_to) {
                        return $card->where('month', '>', $date_from->month)->where('month', '<', $date_to->month)
                            ;
                    });
            });
        dd($q->count());
//        $q->whereHas('timecard', function($card) use ($date_from, $date_to) {
//            return $card->where('month', '>=', $date_from->month)
//                        ->where('month', '<=', $date_to->month)
//                        ->whereYear('timecards.created_at', '>=', $date_from->year)
//                        ->whereYear('timecards.created_at', '<=', $date_to->year);
//        })->whereHas('timecardDay', function($days) use ($date_from, $date_to) {
//            return $days->where('day', '>=', $date_from->day)
//                        ->where('day', '<=', $date_to->day);
//        });

        return $q;
    }
    // Getters
    // TODO write description, Max :^)
    public function getProjectNameAttribute()
    {
        if ($this->project_id) {
            return $this->project->name;
        }
        return null;
    }

    /**
     * Function take year and month from time record
     * timecard and concatenate record timecard day here.
     * Something like this timecard->year-timecard->month-timecard_day->day
     * @return string
     */
    public function getDateAttribute(): string
    {
        // This is for pretty formatting (4 switch to 04)
        $month = now()->month($this->timecard->month)->format('m');
        $day = now()->day($this->timecardDay->day)->format('d');
        return "{$this->timecard->year}-{$month}-{$day}";
    }

    // Relations
    /**
     * Relation to parent timecard day
     * @return BelongsTo
     */
    public function timecardDay(): BelongsTo
    {
        return $this->belongsTo(TimecardDay::class, 'timecard_day_id', 'id');
    }

    public function timecard()
    {
        return $this->hasOneThrough(Timecard::class, TimecardDay::class, 'id', 'id', 'timecard_day_id', 'timecard_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tariff_rate()
    {
        return $this->belongsTo(TariffRates::class, 'tariff_id');
    }

    /**
     * returns name of the tariff rate
     * gets it from relation
     * @return string
     */
    public function getTariffNameAttribute()
    {
        return $this->tariff_rate()->first()->name ?? '';
    }
    // Methods
    /**
     * Function can calculate time period space in hours
     * @return int
     */
    public function getTimePeriodHours(): int
    {
        if ($this->type === self::TYPES_ENG['time periods'] && ! empty($this->end)) {
            $start = Carbon::createFromFormat('H-m', $this->start);
            $end = Carbon::createFromFormat('H-m', $this->end);
            $minutes = $end->diffInMinutes($start);
            return $minutes;
        }

        return 0;
    }
}
