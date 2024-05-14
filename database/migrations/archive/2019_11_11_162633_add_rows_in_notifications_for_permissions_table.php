<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRowsInNotificationsForPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('notifications_for_permissions')->insert([
            // Tasks-related notifications
            [
                'notification_id' => 52,
                'permission' => 'tasks_default_myself',
            ],
            [
                'notification_id' => 52,
                'permission' => 'tasks_default_others',
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
        DB::beginTransaction();

        DB::table('notifications_for_permissions')->where('notification_id', 52)->delete();

        DB::commit();
    }
}
