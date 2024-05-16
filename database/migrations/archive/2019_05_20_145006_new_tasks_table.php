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
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['questionnaire_token', 'is_sent', 'sent_to', 'com_offer_id', 'com_offer_added', 'status_result_call']);
            $table->unsignedInteger('target_id')->nullable();
            $table->integer('notify_send')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('target_id');
            $table->string('questionnaire_token')->nullable();
            $table->boolean('is_sent')->default(0);
            $table->unsignedInteger('sent_to')->nullable();
            $table->string('com_offer_id')->nullable();
            $table->boolean('com_offer_added')->default(0);
            $table->unsignedInteger('status_result_call')->nullable();
            $table->boolean('notify_send', 0)->change();
        });
    }
};
