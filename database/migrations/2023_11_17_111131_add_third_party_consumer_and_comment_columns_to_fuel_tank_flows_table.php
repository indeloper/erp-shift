<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThirdPartyConsumerAndCommentColumnsToFuelTankFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->string('third_party_consumer')->nullable()->after('our_technic_id')->comment('Сторонний потребитель топлива');
            $table->string('comment')->nullable()->after('document')->comment('Комментарий');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('third_party_consumer');
            $table->dropColumn('comment');
        });
    }
}
