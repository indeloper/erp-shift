<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        Schema::create('timecard_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timecard_day_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('type');
            $table->unsignedInteger('tariff_id')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->double('length', 8, 3)->nullable();
            $table->integer('amount')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('commentary')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timecard_day_id', 'type']);
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timecard_records');
    }
};
