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
        Schema::create('q3w_material_comments', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->comment('Уникальный идентификатор');
            $table->text('comment')->comment('Комментарий');
            $table->integer('author_id')->index()->unsigned()->comment('Идентификатор автора комментария');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_materials', function (Blueprint $table) {
            $table->bigInteger('comment_id')->unsigned()->nullable()->comment('Комментарий');

            $table->foreign('comment_id')->references('id')->on('q3w_material_comments');
        });

        Schema::create('q3w_operation_material_comments', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->comment('Уникальный идентификатор');
            $table->text('comment')->comment('Комментарий');
            $table->integer('author_id')->index()->unsigned()->comment('Идентификатор автора комментария');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_operation_materials', function (Blueprint $table) {
            $table->bigInteger('comment_id')->unsigned()->nullable()->comment('Комментарий');
            $table->bigInteger('initial_comment_id')->unsigned()->nullable()->comment('Начальный комментарий');

            $table->foreign('comment_id')->references('id')->on('q3w_operation_material_comments');
            $table->foreign('initial_comment_id')->references('id')->on('q3w_material_comments');
        });

        Schema::create('q3w_material_snapshot_material_comments', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->comment('Уникальный идентификатор');
            $table->text('comment')->comment('Комментарий');
            $table->integer('author_id')->index()->unsigned()->comment('Идентификатор автора комментария');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_material_snapshot_materials', function (Blueprint $table) {
            $table->bigInteger('comment_id')->unsigned()->nullable()->comment('Комментарий');

            $table->foreign('comment_id')->references('id')->on('q3w_material_snapshot_material_comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q3w_materials', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
            $table->dropColumn(['comment_id']);
        });

        Schema::table('q3w_operation_materials', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
            $table->dropColumn(['comment_id']);

            $table->dropForeign(['initial_comment_id']);
            $table->dropColumn(['initial_comment_id']);
        });

        Schema::table('q3w_material_snapshot_materials', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
            $table->dropColumn(['comment_id']);
        });

        Schema::dropIfExists('q3w_material_comments');
        Schema::dropIfExists('q3w_operation_material_comments');
        Schema::dropIfExists('q3w_material_snapshot_material_comments');

    }
};
