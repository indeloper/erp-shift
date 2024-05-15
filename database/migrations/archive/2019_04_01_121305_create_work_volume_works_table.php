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
        Schema::create('work_volume_works', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('work_volume_id');
            $table->unsignedInteger('manual_work_id');
            $table->float('count', 10, 2)->nullable();
            $table->unsignedInteger('term')->nullable();
            $table->boolean('is_tongue');
            $table->float('price_per_one', 15, 2)->nullable();
            $table->float('result_price', 15, 2)->nullable();
            $table->unsignedInteger('subcontractor_id')->nullable();
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
        Schema::dropIfExists('work_volume_works');
    }
};
