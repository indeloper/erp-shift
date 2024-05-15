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
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->unsignedInteger('object_id')->nullable()->after('fuel_tank_id')->comment('ID объекта');
            $table->foreign('object_id')->references('id')->on('project_objects');

            $table->unsignedInteger('company_id')->nullable()->after('object_id')->comment('ID организации');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropForeign(['object_id']);
            $table->dropColumn('object_id');

            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
