<?php

namespace App\Observers;

use App\Models\HumanResources\JobCategory;
use Illuminate\Support\Facades\DB;

class JobCategoryObserver
{
    /**
     * Handle the job category "created" event.
     *
     * @param  JobCategory  $jobCategory
     * @return void
     */
    public function created(JobCategory $jobCategory)
    {
        DB::beginTransaction();

        $jobCategory->generateAction();

        DB::commit();
    }

    /**
     * Handle the job category "updated" event.
     *
     * @param  JobCategory  $jobCategory
     * @return void
     */
    public function updated(JobCategory $jobCategory)
    {
        DB::beginTransaction();

        $jobCategory->generateAction('update');

        DB::commit();
    }

    /**
     * Handle the job category "deleted" event.
     *
     * @param  JobCategory  $jobCategory
     * @return void
     */
    public function deleted(JobCategory $jobCategory)
    {
        DB::beginTransaction();

        $jobCategory->generateAction('destroy');
        $jobCategory->users->each(function ($user) {
            $user->update(['job_category_id' => null]);
        });

        DB::commit();
    }

    /**
     * Handle the job category "restored" event.
     *
     * @param  JobCategory  $jobCategory
     * @return void
     */
    public function restored(JobCategory $jobCategory)
    {
        //
    }

    /**
     * Handle the job category "force deleted" event.
     *
     * @param  JobCategory  $jobCategory
     * @return void
     */
    public function forceDeleted(JobCategory $jobCategory)
    {
        //
    }
}
