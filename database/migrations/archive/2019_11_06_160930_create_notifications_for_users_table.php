<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsForUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('notifications_for_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('notification_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('notifications_for_users')->insert([
            // Tasks-related notifications
            [
                'notification_id' => 6,
                'user_id' => 27,
            ],
            [
                'notification_id' => 6,
                'user_id' => 54,
            ],
            [
                'notification_id' => 7,
                'user_id' => 27,
            ],
            [
                'notification_id' => 7,
                'user_id' => 54,
            ],
            [
                'notification_id' => 22,
                'user_id' => 27,
            ],
            [
                'notification_id' => 23,
                'user_id' => 27,
            ],
            [
                'notification_id' => 26,
                'user_id' => 27,
            ],
            [
                'notification_id' => 27,
                'user_id' => 27,
            ],
            [
                'notification_id' => 29,
                'user_id' => 27,
            ],
            [
                'notification_id' => 29,
                'user_id' => 54,
            ],
            [
                'notification_id' => 34,
                'user_id' => 27,
            ],
            [
                'notification_id' => 34,
                'user_id' => 54,
            ],
            [
                'notification_id' => 37,
                'user_id' => 27,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications_for_users');
    }
}
