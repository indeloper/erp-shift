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
        Schema::create('commercial_offer_works', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('commercial_offer_id');
            $table->unsignedInteger('work_volume_work_id')->nullable();
            $table->unsignedInteger('manual_work_id');
            $table->float('count', 10, 3)->nullable();
            $table->unsignedInteger('term')->nullable();
            $table->boolean('is_tongue');
            $table->float('price_per_one', 15, 2)->nullable();
            $table->float('result_price', 15, 2)->nullable();
            $table->unsignedInteger('subcontractor_file_id')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->integer('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_offer_works');
    }
};
