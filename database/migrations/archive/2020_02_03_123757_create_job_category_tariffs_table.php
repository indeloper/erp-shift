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

        Schema::create('job_category_tariffs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_category_id')->index();
            $table->unsignedInteger('tariff_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->float('rate', 8, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_category_tariffs');
    }
};
