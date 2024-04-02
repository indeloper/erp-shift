<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title')
                ->comment('Заголовок меню');


            $table->unsignedBigInteger(
                'parent_id'
            )
                ->nullable()
                ->comment('Для подэлементов');

            $table->string('route_name')
                ->nullable()
                ->comment('Название роута');

            $table->boolean('is_su')
                ->comment('is super users')
                ->default(false);

            $table->string('icon_path')
                ->nullable();

            $table->json('gates')
                ->nullable();

            $table->json('actives')
                ->nullable();

            $table->boolean('status')
                ->default(false);

            $table->softDeletes();

            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('menu_items')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
}
