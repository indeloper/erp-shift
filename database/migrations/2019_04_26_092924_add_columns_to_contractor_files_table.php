<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToContractorFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractor_files', function (Blueprint $table) {
            $table->unsignedInteger('commercial_offer_id');
            $table->unsignedInteger('contractor_id');
            $table->string('file_name');
            $table->string('original_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractor_files', function (Blueprint $table) {
            $table->dropColumn('commercial_offer_id');
            $table->dropColumn('contractor_id');
            $table->dropColumn('file_name');
            $table->dropColumn('original_name');
        });
    }
}
