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
    public function up(): void
    {
        Schema::create('exception_notification_users', function (Blueprint $table) {
            $table->unsignedBigInteger(
                'notification_item_id'
            );

            $table->unsignedInteger('user_id');

            $table->string('channel');

            $table->primary(['notification_item_id', 'user_id', 'channel'], 'primary_exception_notification_users');

            $table->foreign('notification_item_id')
                ->references('id')
                ->on('notification_items')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('exception_notification_users');
    }
};
