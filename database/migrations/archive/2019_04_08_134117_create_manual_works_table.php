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
        Schema::create('manual_works', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('work_group_id')->nullable();
            $table->string('name', 150);
            $table->string('description', 200)->nullable();
            $table->float('price_per_unit', 20, 2)->nullable();
            $table->string('unit', 15)->nullable();
            $table->string('unit_per_days', 5)->nullable();
            $table->string('nds', 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_works');
    }
};
