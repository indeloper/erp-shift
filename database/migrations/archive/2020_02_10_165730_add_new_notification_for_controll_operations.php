<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    const NOTIFICATION_TYPE = 93;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        $new_type = [
            'id' => self::NOTIFICATION_TYPE,
            'group' => 2,
            'name' => 'Уведомление об отклонении операции',
            'for_everyone' => 0, // for groups
        ];

        DB::table('notification_types')->insert($new_type);

        $notification_groups = [
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 27,
            ],
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 8,
            ],
        ];

        DB::table('notifications_for_groups')->insert($notification_groups);

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
