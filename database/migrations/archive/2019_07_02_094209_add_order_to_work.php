<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\WorkVolume\WorkVolumeWork;
use App\Models\WorkVolume\WorkVolume;

class AddOrderToWork extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(1);
        });

        $work_volume_works = \App\Models\WorkVolume\WorkVolumeWork::get()->groupBy('work_volume_id');

        foreach ($work_volume_works as $works) {
            foreach ($works as $key => $work) {
                $work->order = $key;
                
                $work->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
