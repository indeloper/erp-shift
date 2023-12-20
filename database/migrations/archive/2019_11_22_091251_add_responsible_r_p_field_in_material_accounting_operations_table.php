<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResponsibleRPFieldInMaterialAccountingOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->unsignedInteger('responsible_RP')->nullable()->after('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_accounting_operations', function (Blueprint $table) {
            $table->dropColumn('responsible_RP');
        });
    }
}
