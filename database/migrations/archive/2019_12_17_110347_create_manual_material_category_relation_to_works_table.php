<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('manual_material_category_relation_to_works', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('manual_material_category_id');
            $table->unsignedInteger('work_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_material_category_relation_to_works');
    }
};
