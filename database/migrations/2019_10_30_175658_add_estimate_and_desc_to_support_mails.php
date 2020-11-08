<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstimateAndDescToSupportMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_mails', function (Blueprint $table) {
            $table->unsignedInteger('estimate')->nullable();
            $table->text('result_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_mails', function (Blueprint $table) {
            $table->dropColumn('estimate');
            $table->dropColumn('result_description');
        });
    }
}
