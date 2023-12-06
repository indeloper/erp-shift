<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddNewPermissionsForTechTicketsAndDefects extends Migration
{
    const PERMISSION_CODENAMES = [
        'tech_acc_our_technic_tickets_see',
        'tech_acc_defects_see',
    ];

    const PERMISSION_NAMES = [
        'Просмотр всех заявок на технику',
        'Просмотр всех заявок на неисправность',
    ];

    public function up()
    {
        $insert = [];

        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => 13,
                "name" => self::PERMISSION_NAMES[$key],
                "codename" => $codename,
                'created_at' => now()
            ];
        }

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 47,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionTwo,
                'created_at' => now()
            ],
        ]);

        DB::table('user_permissions')->insert([
            [
                'user_id' => User::HARDCODED_PERSONS['router'],
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'user_id' => User::HARDCODED_PERSONS['router'],
                'permission_id' => $permissionTwo,
                'created_at' => now()
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
        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionOne)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionTwo)->delete();
        DB::table('user_permissions')->where('permission_id', $permissionTwo)->delete();
        DB::table('user_permissions')->where('permission_id', $permissionTwo)->delete();
        DB::table('permissions')->whereIn('codename', self::PERMISSION_CODENAMES)->delete();

        DB::commit();
    }
}
