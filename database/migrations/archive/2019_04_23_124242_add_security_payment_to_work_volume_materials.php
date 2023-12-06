<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecurityPaymentToWorkVolumeMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->float('security_price_one', 15, 2)->nullable();
            $table->float('security_price_result', 15, 2)->nullable();        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->dropColumn('security_price_one');
            $table->dropColumn('security_price_result');
        });
    }
}
