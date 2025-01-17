<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Material Accounting notifications
            [
                'id' => 64,
                'group' => 2,
                'name' => 'Уведомление об обновлении запроса на создание операции',
                'for_everyone' => 0,
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            // Material Accounting notifications
            [
                'notification_id' => 64,
                'group_id' => 8,
            ],
            [
                'notification_id' => 64,
                'group_id' => 19,
            ],
            [
                'notification_id' => 64,
                'group_id' => 27,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', 64)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 64)->delete();

        DB::commit();
    }
};
