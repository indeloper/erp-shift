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
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropColumn('fuel_level');
        });
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->integer('fuel_level')->nullable()->after('company_id')->comment('Текущий уровень топлива');
        });
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropColumn('object_id');
        });
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->unsignedInteger('object_id')->nullable()->after('tank_number')->comment('ID объекта');
            $table->foreign('object_id')->references('id')->on('project_objects');
        });

        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->dropColumn('volume');
        });
        Schema::table('fuel_tank_flows', function (Blueprint $table) {
            $table->integer('volume')->nullable()->after('fuel_tank_flow_type_id')->comment('Количество топлива');
        });

        Schema::table('fuel_tank_flow_remains', function (Blueprint $table) {
            $table->dropColumn('volume');
        });
        Schema::table('fuel_tank_flow_remains', function (Blueprint $table) {
            $table->integer('volume')->nullable()->after('fuel_tank_id')->comment('Количество топлива');
        });

        Schema::table('fuel_tank_movements', function (Blueprint $table) {
            $table->dropColumn('fuel_level');
        });
        Schema::table('fuel_tank_movements', function (Blueprint $table) {
            $table->integer('fuel_level')->nullable()->after('previous_object_id')->comment('Текущий уровень топлива');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropForeign(['object_id']);
        });
    }
};
