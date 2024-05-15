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
            // Material Accounting Notifications
            [
                'id' => 55,
                'group' => 2,
                'name' => 'Уведомление об отмене операции',
                'for_everyone' => 1,
            ],
            [
                'id' => 56,
                'group' => 2,
                'name' => 'Уведомление о запросе на согласование создания операции',
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 57,
                'group' => 2,
                'name' => 'Уведомление о согласовании черновика операции',
                'for_everyone' => 0, // for permissions
            ],
            [
                'id' => 58,
                'group' => 2,
                'name' => 'Уведомление об отклонении черновика операции',
                'for_everyone' => 0, // for permissions
            ],
            [
                'id' => 59,
                'group' => 2,
                'name' => 'Уведомление о частичном закрытии операции',
                'for_everyone' => 0, // for permissions
            ],
            [
                'id' => 60,
                'group' => 2,
                'name' => 'Уведомление о завершении операции',
                'for_everyone' => 0, // for permissions
            ],
            [
                'id' => 61,
                'group' => 2,
                'name' => 'Уведомление о подтверждении операции',
                'for_everyone' => 1,
            ],
            [
                'id' => 62,
                'group' => 2,
                'name' => 'Уведомление о переводе статуса операции в конфликт',
                'for_everyone' => 1,
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            // Material Accounting Notifications
            [
                'notification_id' => 56,
                'group_id' => 19,
            ],
            [
                'notification_id' => 56,
                'group_id' => 27,
            ],
            [
                'notification_id' => 56,
                'group_id' => 8,
            ],
        ]);

        DB::table('notifications_for_permissions')->insert([
            // Material Accounting Notifications
            [
                'notification_id' => 57,
                'permission' => 'mat_acc_moving_draft_create',
            ],
            [
                'notification_id' => 57,
                'permission' => 'mat_acc_write_off_draft_create',
            ],
            [
                'notification_id' => 57,
                'permission' => 'mat_acc_transformation_draft_create',
            ],
            [
                'notification_id' => 57,
                'permission' => 'mat_acc_arrival_draft_create',
            ],
            [
                'notification_id' => 58,
                'permission' => 'mat_acc_moving_draft_create',
            ],
            [
                'notification_id' => 58,
                'permission' => 'mat_acc_write_off_draft_create',
            ],
            [
                'notification_id' => 58,
                'permission' => 'mat_acc_transformation_draft_create',
            ],
            [
                'notification_id' => 58,
                'permission' => 'mat_acc_arrival_draft_create',
            ],
            [
                'notification_id' => 59,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 59,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 59,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 59,
                'permission' => 'mat_acc_arrival_create',
            ],
            [
                'notification_id' => 60,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 60,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 60,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 60,
                'permission' => 'mat_acc_arrival_create',
            ],
        ]);

        // update notification with 12 type
        DB::table('notification_types')->where('id', 12)->update(['for_everyone' => 1]);
        DB::table('notifications_for_permissions')->where('notification_id', 12)->delete();

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', 55)->delete();
        DB::table('notification_types')->where('id', 56)->delete();
        DB::table('notification_types')->where('id', 57)->delete();
        DB::table('notification_types')->where('id', 58)->delete();
        DB::table('notification_types')->where('id', 59)->delete();
        DB::table('notification_types')->where('id', 60)->delete();
        DB::table('notification_types')->where('id', 61)->delete();
        DB::table('notification_types')->where('id', 62)->delete();

        DB::table('notifications_for_groups')->where('notification_id', 56)->delete();
        DB::table('notifications_for_permissions')->where('notification_id', 57)->delete();
        DB::table('notifications_for_permissions')->where('notification_id', 58)->delete();
        DB::table('notifications_for_permissions')->where('notification_id', 59)->delete();
        DB::table('notifications_for_permissions')->where('notification_id', 60)->delete();

        DB::table('notification_types')->where('id', 12)->update(['for_everyone' => 0]);
        DB::table('notifications_for_permissions')->insert([
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_moving_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_write_off_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_transformation_create',
            ],
            [
                'notification_id' => 12,
                'permission' => 'mat_acc_arrival_create',
            ],
        ]);

        DB::commit();
    }
};
