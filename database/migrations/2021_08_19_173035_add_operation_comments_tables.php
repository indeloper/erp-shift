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
            $table->text('comment')->nullable()->comment('Комментарий');
        });

        Schema::table('q3w_operation_materials', function(Blueprint $table) {
            $table->text('comment')->nullable()->comment('Комментарий');
            $table->text('initial_comment')->nullable()->comment('Начальный комментарий материала при создании операции');
        });

        Schema::table('q3w_material_snapshot_materials', function(Blueprint $table) {
            $table->text('comment')->nullable()->comment('Комментарий');
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

        Schema::table('q3w_material_snapshot_materials', function(Blueprint $table) {
            $table->dropColumn(['comment']);
        });
    }
}
