<?php

namespace App\Models\HumanResources;

use App\Traits\Logable;
use App\Models\{Project, User};
use Illuminate\Database\Eloquent\{Model, Builder, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class TimecardAddition extends Model
{
    use SoftDeletes, Logable;

    protected $fillable = [
        'timecard_id',
        'user_id',
        'project_id',
        'type',
        'name',
        'amount',
        'prolonged'
    ];

    protected $appends = ['type_name'];

    public const TYPES = [
        1 => 'Компенсация',
        2 => 'Штраф',
        3 => 'Премия',
    ];

    public const TYPES_ENG = [
        'compensation' => 1,
        'fine' => 2,
        'bonus' => 3,
    ];

    // Scopes

    /**
     * Scope for search by type
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeByType(Builder $query, Request $request): Builder
    {
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return $query->distinct()->select(['id', 'name']);
    }

    // Getters
    /**
     * Getter for type_name attribute
     * @return mixed
     */
    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type];
    }

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
     * Relation to timecard addition creator
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relation to timecard addition project
     * (works only for bonuses)
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    // Methods
}
