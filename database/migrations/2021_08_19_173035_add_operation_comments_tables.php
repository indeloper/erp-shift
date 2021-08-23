<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOperationCommentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q3w_materials', function(Blueprint $table) {
            $table->multiLineString('comment')->nullable()->comment('Комментарий');
        });

        Schema::table('q3w_operation_materials', function(Blueprint $table) {
            $table->multiLineString('comment')->nullable()->comment('Комментарий');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q3w_materials', function(Blueprint $table) {
            $table->dropColumn(['comment']);
        });

        Schema::table('q3w_operation_materials', function(Blueprint $table) {
            $table->dropColumn(['comment']);
        });
    }
}
