<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOptionColumnInWorkVolumesAndCommercialOffersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->string('option')->default('По умолчанию')->change();
        });
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->string('option')->default('По умолчанию')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volumes', function (Blueprint $table) {
            $table->string('option')->nullable()->change();
        });
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->string('option')->nullable()->change();
        });
    }
}
