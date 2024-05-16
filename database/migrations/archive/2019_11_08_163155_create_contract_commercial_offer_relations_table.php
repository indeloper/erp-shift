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
    public function up()
    {
        Schema::create('contract_commercial_offer_relations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('contract_id')->index();
            $table->unsignedInteger('commercial_offer_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_commercial_offer_relations');
    }
};
