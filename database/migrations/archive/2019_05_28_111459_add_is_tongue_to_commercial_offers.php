<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTongueToCommercialOffers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->boolean('is_tongue')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commercial_offers', function (Blueprint $table) {
            $table->dropColumn('is_tongue');
        });
    }
}
