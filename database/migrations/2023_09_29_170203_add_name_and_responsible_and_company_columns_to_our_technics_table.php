<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameAndResponsibleAndCompanyColumnsToOurTechnicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id')->comment('Наименование');

            $table->unsignedInteger('responsible_id')->nullable()->after('name')->comment('ID автора');
            $table->foreign('responsible_id')->references('id')->on('users');

            $table->unsignedInteger('company_id')->nullable()->after('responsible_id')->comment('ID организации-собственника');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('our_technics', function (Blueprint $table) {
            
            $table->dropColumn('name');
            $table->dropForeign(['responsible_id']);
            $table->dropColumn('responsible_id');
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
}
