<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о удалении заявки на неисправность';

    const NOTIFICATION_TYPE_IDS = [68, 69, 70, 71, 72];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $new_types = [
            [
                'id' => 68,
                'group' => 10,
                'name' => 'Уведомление о согласовании заявки на технику',
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 69,
                'group' => 10,
                'name' => 'Уведомление о задаче начале использования',
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 70,
                'group' => 10,
                'name' => 'Уведомление о необходимости обработки заявки',
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 71,
                'group' => 10,
                'name' => 'Уведомление о подтверждении отправки техники',
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 72,
                'group' => 10,
                'name' => 'Уведомление о подтверждении получения техники',
                'for_everyone' => 0, // for groups
            ],
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
    public function down()
    {
        DB::beginTransaction();

        DB::table('notification_types')->whereIn('id', self::NOTIFICATION_TYPE_IDS)->delete();
        DB::table('notifications_for_groups')->whereIn('notification_id', self::NOTIFICATION_TYPE_IDS)->delete();

        DB::commit();
    }
};
