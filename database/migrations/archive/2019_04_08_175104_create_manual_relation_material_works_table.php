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
        Schema::create('manual_relation_material_works', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('manual_material_id')->nullable();
            $table->unsignedInteger('manual_work_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_relation_material_works');
    }
};
