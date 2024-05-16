<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPermissionsForOurTechnic extends Migration
{
    const PERMISSION_CODENAMES = [
        'tech_acc_our_technic_create',
        'tech_acc_our_technic_edit',
        'tech_acc_our_technic_delete',
    ];

    const PERMISSION_NAMES = [
        'Создание экземпляра техники',
        'Изменение экземпляра техники',
        'Удаление экземпляра техники',
    ];

    public function up()
    {
        $insert = [];

        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => 13,
                'name' => self::PERMISSION_NAMES[$key],
                'codename' => $codename,
                'created_at' => now(),
            ];
        }

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;
        $permissionThree = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[2])->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 47,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionThree,
                'created_at' => now(),
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
        $permissionThree = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[2])->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionOne)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionTwo)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionThree)->delete();
        DB::table('permissions')->whereIn('codename', self::PERMISSION_CODENAMES)->delete();

        DB::commit();
    }
}
