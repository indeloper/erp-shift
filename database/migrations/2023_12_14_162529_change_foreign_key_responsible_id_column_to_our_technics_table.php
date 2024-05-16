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
    public function up()
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
        });

        Schema::table('our_technics', function (Blueprint $table) {
            $table->bigInteger('responsible_id')->nullable()->unsigned()->comment('ID ответственного employee')->change();
            $table->foreign('responsible_id')->references('id')->on('employees');
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
            $table->dropForeign(['responsible_id']);
        });
    }
};
