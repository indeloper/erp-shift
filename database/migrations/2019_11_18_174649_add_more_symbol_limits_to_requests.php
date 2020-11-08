<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreSymbolLimitsToRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_requests', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->text('result_comment')->nullable()->change();
        });

        Schema::table('commercial_offer_requests', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->text('result_comment')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volume_requests', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->string('result_comment')->nullable()->change();
        });

        Schema::table('commercial_offer_requests', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->string('result_comment')->nullable()->change();
        });
    }
}
