<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_TYPE = 86;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        $new_type = [
            'id' => self::NOTIFICATION_TYPE,
            'group' => 10,
            'name' => 'Уведомление об освобождении техники',
            'for_everyone' => 0, // for groups
        ];

        DB::table('notification_types')->insert($new_type);

        $notification_group = [
            'notification_id' => self::NOTIFICATION_TYPE,
            'group_id' => 47,
        ];

        DB::table('notifications_for_groups')->insert($notification_group);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', self::NOTIFICATION_TYPE)->delete();
        DB::table('notifications_for_groups')->where('notification_id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
};
