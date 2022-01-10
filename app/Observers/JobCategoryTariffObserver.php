<?php

namespace App\Observers;

use App\Models\HumanResources\JobCategoryTariff;
use Illuminate\Support\Facades\DB;

class JobCategoryTariffObserver
{
    /**
     * Handle the job category tariff "created" event.
     *
     * @param  JobCategoryTariff  $jobCategoryTariff
     * @return void
     */
    public function created(JobCategoryTariff $jobCategoryTariff)
    {
        DB::beginTransaction();

        $jobCategoryTariff->generateAction();

        DB::commit();
    }

    /**
     * Handle the job category tariff "updated" event.
     *
     * @param  JobCategoryTariff  $jobCategoryTariff
     * @return void
     */
    public function updated(JobCategoryTariff $jobCategoryTariff)
    {
        DB::beginTransaction();

        $jobCategoryTariff->generateAction('update');

        DB::commit();
    }

    /**
     * Handle the job category tariff "deleted" event.
     *
     * @param  JobCategoryTariff  $jobCategoryTariff
     * @return void
     */
    public function deleted(JobCategoryTariff $jobCategoryTariff)
    {
        DB::beginTransaction();

        $jobCategoryTariff->generateAction('destroy');

        DB::commit();
    }

    /**
     * Handle the job category tariff "restored" event.
     *
     * @param  JobCategoryTariff  $jobCategoryTariff
     * @return void
     */
    public function restored(JobCategoryTariff $jobCategoryTariff)
    {
        //
    }

    /**
     * Handle the job category tariff "force deleted" event.
     *
     * @param  JobCategoryTariff  $jobCategoryTariff
     * @return void
     */
    public function forceDeleted(JobCategoryTariff $jobCategoryTariff)
    {
        //
    }
}
