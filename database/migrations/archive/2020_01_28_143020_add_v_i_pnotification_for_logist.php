<?php

use App\Models\User;
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

        $notification_group = [
            'notification_id' => self::NOTIFICATION_TYPE,
            'user_id' => User::HARDCODED_PERSONS['router'],
        ];

        DB::table('notifications_for_users')->insert($notification_group);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notifications_for_users')->where('notification_id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
};
