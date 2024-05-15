<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    const NOTIFICATION_TYPE = 95;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::beginTransaction();

        $new_type = [
            'id' => self::NOTIFICATION_TYPE,
            'group' => 2,
            'name' => 'Уведомление о задаче контроль операции',
            'for_everyone' => 0, // for groups
        ];

        DB::table('notification_types')->insert($new_type);

        $notification_groups = [
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 6,
            ],
        ];

        DB::table('notifications_for_groups')->insert($notification_groups);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', self::NOTIFICATION_TYPE)->delete();
        DB::table('notifications_for_groups')->where('notification_id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
};
