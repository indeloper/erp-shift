<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThesisIdToContractRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_requests', function (Blueprint $table) {
            $table->unsignedInteger('thesis_id')->nullable();
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('contract_requests', function (Blueprint $table) {
            $table->dropColumn('thesis_id');
        });
    }
}
