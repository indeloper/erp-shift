<?php

namespace App\Observers;

use App\Models\HumanResources\ReportGroup;
use Illuminate\Support\Facades\DB;

class ReportGroupObserver
{
    /**
     * Handle the report group "created" event.
     *
     * @param  ReportGroup  $reportGroup
     * @return void
     */
    public function created(ReportGroup $reportGroup)
    {
        DB::beginTransaction();

        $reportGroup->generateAction();

        DB::commit();
    }

    /**
     * Handle the report group "updated" event.
     *
     * @param  ReportGroup  $reportGroup
     * @return void
     */
    public function updated(ReportGroup $reportGroup)
    {
        DB::beginTransaction();

        $reportGroup->generateAction('update');

        DB::commit();
    }

    /**
     * Handle the report group "deleted" event.
     *
     * @param  ReportGroup  $reportGroup
     * @return void
     */
    public function deleted(ReportGroup $reportGroup)
    {
        DB::beginTransaction();

        $reportGroup->generateAction('deleted');
        $reportGroup->jobCategories->each(function ($jobCategory) {
           $jobCategory->update(['report_group_id' => null]);
        });

        DB::commit();
    }

    /**
     * Handle the report group "restored" event.
     *
     * @param  ReportGroup  $reportGroup
     * @return void
     */
    public function restored(ReportGroup $reportGroup)
    {
        //
    }

    /**
     * Handle the report group "force deleted" event.
     *
     * @param  ReportGroup  $reportGroup
     * @return void
     */
    public function forceDeleted(ReportGroup $reportGroup)
    {
        //
    }
}
