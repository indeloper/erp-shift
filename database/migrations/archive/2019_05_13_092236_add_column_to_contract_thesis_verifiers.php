<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToContractThesisVerifiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_thesis_verifiers', function (Blueprint $table) {
            $table->unsignedInteger('thesis_id');
            $table->string('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_thesis_verifiers', function (Blueprint $table) {
            $table->dropColumn('thesis_id');
            $table->dropColumn('status');
        });
    }
}
