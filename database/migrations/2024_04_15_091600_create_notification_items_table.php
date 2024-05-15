<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateNotificationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('type')->comment('Тип уведомления');

            $table->string('class')->comment('Класс увудомления');

            $table->string('description')->comment('Описание уведомления');

            $table->boolean('status');

            $table->timestamps();
        });

        Schema::create('notification_item_permission', function (Blueprint $table) {
            $table->unsignedBigInteger('notification_item_id');
            $table->unsignedInteger('permission_id');

            $table->foreign('notification_item_id')
                ->references('id')
                ->on('notification_items')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Artisan::call('db:seed --class=NotificationSeeder');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_item_permission');
        Schema::dropIfExists('notification_items');
    }
}
