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
    public function down(): void
    {
        Schema::table('support_mails', function (Blueprint $table) {
            $table->dropColumn('estimate');
            $table->dropColumn('result_description');
        });
    }
};
