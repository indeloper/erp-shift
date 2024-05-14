<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSeeTechnicTicketModulePermission extends Migration
{
    const PERMISSION_CODENAMES = [
        'tech_acc_see_technic_ticket_module',
    ];

    const PERMISSION_NAMES = [
        'Просмотр раздела Учет Топлива',
    ];

    public function up()
    {
        $insert = [];
        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => 17,
                'name' => self::PERMISSION_NAMES[$key],
                'codename' => $codename,
                'created_at' => now(),
            ];
        }

        $groups = array_merge(Group::PROJECT_MANAGERS, Group::FOREMEN, Group::MECHANICS, Group::where('department_id', 8)->get()->pluck('id')->toArray());
        $permissionTwo = DB::table('permissions')->where('codename', 'tech_acc_our_technic_tickets_see')->first()->id;

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;

        DB::table('group_permissions')->where('permission_id', $permissionTwo)->whereIn('group_id', Group::FOREMEN)->delete();

        foreach ($groups as $group) {
            DB::table('group_permissions')->insert([
                [
                    'group_id' => $group,
                    'permission_id' => $permissionOne,
                    'created_at' => now(),
                ],
            ]);
        }
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', 'tech_acc_our_technic_tickets_see')->first()->id;
        $groups = array_merge(Group::PROJECT_MANAGERS, Group::FOREMEN, Group::MECHANICS, Group::where('department_id', 8)->get()->pluck('id')->toArray());

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionOne)->whereIn('group_id', $groups)->delete();

        foreach (Group::FOREMEN as $group) {
            DB::table('group_permissions')->insert([
                [
                    'group_id' => $group,
                    'permission_id' => $permissionTwo,
                    'created_at' => now(),
                ],
            ]);
        }
        DB::commit();
    }
}
