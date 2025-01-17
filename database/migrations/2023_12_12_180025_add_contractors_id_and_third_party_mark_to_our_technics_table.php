<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->boolean('third_party_mark')->default(false)->after('company_id')->comment('Техника стороннего контрагента');

            $table->unsignedInteger('contractor_id')->nullable()->after('third_party_mark')->comment('ID контрагента');
            $table->foreign('contractor_id')->references('id')->on('contractors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->dropColumn('third_party_mark');

            $table->dropForeign(['contractor_id']);
            $table->dropColumn('contractor_id');
        });
    }
};
