<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsParticipatesInMaterialAccountingFieldToObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->boolean('is_participates_in_material_accounting')->comment('Участвует в материальном учете');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_objects', function (Blueprint $table) {
            $table->dropColumn('is_participates_in_material_accounting');
        });
    }
}
