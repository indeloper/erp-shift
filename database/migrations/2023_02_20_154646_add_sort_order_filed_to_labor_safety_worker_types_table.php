<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSortOrderFiledToLaborSafetyWorkerTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('labor_safety_worker_types', function (Blueprint $table) {
            $table->integer('sort_order')->unsigned()->after('name')->index();
        });

        $start = 10;
        $increment = 10;

        DB::table('labor_safety_worker_types')->orderBy('id')->chunk(100, function ($workerTypes) use ($start, $increment) {
            foreach ($workerTypes as $workerType) {
                DB::table('labor_safety_worker_types')
                    ->where('id', $workerType->id)
                    ->update(['sort_order' => $start]);

                $start += $increment;
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('labor_safety_worker_types', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}