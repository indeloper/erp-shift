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

        Schema::create('report_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->unsignedInteger('user_id')->index();
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
        Schema::dropIfExists('report_groups');
    }
};
