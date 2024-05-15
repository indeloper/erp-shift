<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_TYPE_IDS = [84];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::beginTransaction();

        $new_types = [
            'id' => 84,
            'group' => 10,
            'name' => 'Уведомление об обработке логистом заявки',
            'for_everyone' => 0, // for groups
        ];

        DB::table('notification_types')->insert($new_types);

        $groups = array_merge(Group::PROJECT_MANAGERS, Group::FOREMEN, [47], Group::where('department_id', 8)->get()->pluck('id')->toArray());

        $notification_groups = [];
        foreach (collect($new_types)->pluck('id') as $type_id) {
            foreach ($groups as $group_id) {
                $notification_groups[] = [
                    'notification_id' => $type_id,
                    'group_id' => $group_id,
                ];
            }
        }

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

        DB::table('notification_types')->whereIn('id', self::NOTIFICATION_TYPE_IDS)->delete();
        DB::table('notifications_for_groups')->whereIn('notification_id', self::NOTIFICATION_TYPE_IDS)->delete();

        DB::commit();
    }
};
