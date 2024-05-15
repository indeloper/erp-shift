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
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('questionnaire_token')->nullable();
            $table->boolean('is_sent')->default(0);
            $table->unsignedInteger('sent_to')->nullable();
            $table->string('com_offer_id')->nullable();
            $table->boolean('com_offer_added')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('questionnaire_token');
            $table->dropColumn('is_sent');
            $table->dropColumn('sent_to');
            $table->dropColumn('com_offer_id');
            $table->dropColumn('com_offer_added');
        });
    }
};
