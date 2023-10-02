<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameAndResponsibleColumns extends Migration
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
        });
    }
}
