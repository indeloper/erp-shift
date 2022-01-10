<?php

namespace App\Models\HumanResources;

use App\Models\User;
use App\Traits\HasAuthor;
use App\Traits\Logable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class ReportGroup extends Model
{
    use SoftDeletes, HasAuthor, Logable;

    protected $fillable = ['name', 'user_id'];

    const FILTERS = [
        'name' => 'name', // Название группы
    ];

    // Local Scopes
    /**
     * Return report groups for given filter.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeFilter(Builder $query, Request $request)
    {
        $filters = $request->filters ?? [];
        $values = $request->values ?? [];

        foreach ($filters as $key => $filter) {
            if (in_array($filter, self::FILTERS)) {
                $query->whereIn($filter,(array) $values[$key]);
            }
        }

        return $query;
    }

    // Custom getters

    // Relations
    /**
     * Relation for report group job categories
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobCategories()
    {
        return $this->hasMany(JobCategory::class);
    }

    /**
     * Relation for report group users
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, JobCategory::class);
    }

    // Methods
    /**
     * This function can attach report
     * job categories or detach them
     * @param array $request
     */
    public function updateJobCategories(array $request)
    {
        $jobCategoriesToAttach = $request['job_categories'] ?? [];
        $jobCategoriesToDetach = $request['deleted_job_categories'] ?? [];

        $this->attachJobCategories($jobCategoriesToAttach);
        $this->detachJobCategories($jobCategoriesToDetach);
    }

    /**
     * This function attach job categories to report group
     * @param array $jobCategoriesToAttach
     */
    public function attachJobCategories(array $jobCategoriesToAttach)
    {
        $this->jobCategories()->saveMany(JobCategory::whereIn('id', $jobCategoriesToAttach)->get());
    }

    /**
     * This function detach job categories from report group
     * @param array $jobCategoriesToDetach
     */
    public function detachJobCategories(array $jobCategoriesToDetach)
    {
        JobCategory::whereIn('id', $jobCategoriesToDetach)->get()->each(function ($jobCategory) {
            $jobCategory->update(['report_group_id' => null]);
        });
    }
}
