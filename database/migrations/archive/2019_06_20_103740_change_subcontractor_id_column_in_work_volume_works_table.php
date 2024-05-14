<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSubcontractorIdColumnInWorkVolumeWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            \App\Models\WorkVolume\WorkVolumeWork::query()->update(['subcontractor_id' => null]);
            $table->renameColumn('subcontractor_id', 'subcontractor_file_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->renameColumn('subcontractor_file_id', 'subcontractor_id');
        });
    }
}
