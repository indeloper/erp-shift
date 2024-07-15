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
        Schema::create('w_v_work_material_complects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('complect_name');
            $table->unsignedInteger('work_volume_id');
            $table->unsignedInteger('wv_work_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('w_v_work_material_complects');
    }
};
