<?php

namespace App\Models\HumanResources;

use App\Traits\{AdditionalFunctions, HasAuthor, Logable};
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class JobCategory extends Model
{
    use SoftDeletes, HasAuthor, Logable, AdditionalFunctions;

    protected $fillable = ['name', 'report_group_id', 'user_id'];

    const FILTERS = [
        'name' => 'name', // Название категории
        'report_group_id' => 'report_group_id', // Отчётные группы
    ];

    // Local Scopes
    /**
     * Return job categories for given filter.
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
                if ($filter === self::FILTERS['name']) {
                    $query->where($filter, 'like', "%{$values[$key]}%");
                } else {
                    $query->whereIn($filter,(array) $values[$key]);
                }
            }
        }

        return $query;
    }
    // Custom getters

    // Relations
    /**
     * Relation for job category tariffs
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tariffs()
    {
        return $this->hasMany(JobCategoryTariff::class);
    }

    /**
     * Relation for job category report group
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reportGroup()
    {
        return $this->belongsTo(ReportGroup::class);
    }

    /**
     * Relation for users with this job category
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Methods
    /**
     * This function delete job category tariffs
     * @param array $tariff_ids
     * @throws \Exception
     */
    public function deleteTariffs(array $tariff_ids): void
    {
        foreach ($tariff_ids as $tariff_id) {
            $this->tariffs()->findOrFail($tariff_id)->delete();
        }
    }

    /**
     * This function create or update job category tariffs
     * @param array $tariffs
     * @throws \Exception
     */
    public function updateTariffs(array $tariffs)
    {
        foreach ($tariffs as $tariff) {
            (isset($tariff['id']) and $tariff['id'] != -1)
                ? $this->updateExistedTariff($tariff)
                : $this->createNewTariff($tariff);
        }
    }

    /**
     * This function find job category tariff
     * and update it with new values
     * @param array $tariff
     * @throws \Exception
     */
    public function updateExistedTariff(array $tariff)
    {
        $this->tariffs()->findOrFail($tariff['id'])->update([
            'tariff_id' => $tariff['tariff_id'],
            'rate' => $tariff['rate'] ?? 0,
            'user_id' => $tariff['user_id']
        ]);
    }

    /**
     * This function create new tariff
     * for job category
     * @param array $tariff
     */
    public function createNewTariff(array $tariff)
    {
        $this->tariffs()->create([
            'tariff_id' => $tariff['tariff_id'],
            'rate' => $tariff['rate'] ?? 0,
            'user_id' => $tariff['user_id'],
        ]);
    }

    /**
     * This function can attach job category
     * users or detach them
     * @param array $request
     */
    public function updateUsers(array $request)
    {
        $usersToAttach = $request['user_ids'] ?? [];
        $usersToDetach = $request['deleted_user_ids'] ?? [];

        $this->attachCategoryUsers($usersToAttach);
        $this->detachCategoryUsers($usersToDetach);
    }

    /**
     * This function attach users to job category
     * @param array $usersToAttach
     */
    public function attachCategoryUsers(array $usersToAttach)
    {
        $this->users()->saveMany(User::whereIn('id', $usersToAttach)->get());
    }

    /**
     * This function detach users from job category
     * @param array $usersToDetach
     */
    public function detachCategoryUsers(array $usersToDetach)
    {
        User::whereIn('id', $usersToDetach)->get()->each(function ($user) {
            $user->update(['job_category_id' => null]);
        });
    }
}
