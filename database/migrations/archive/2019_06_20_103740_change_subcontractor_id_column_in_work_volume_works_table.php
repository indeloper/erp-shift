<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            \App\Models\WorkVolume\WorkVolumeWork::query()->update(['subcontractor_id' => null]);
            $table->renameColumn('subcontractor_id', 'subcontractor_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->renameColumn('subcontractor_file_id', 'subcontractor_id');
        });
    }
};
