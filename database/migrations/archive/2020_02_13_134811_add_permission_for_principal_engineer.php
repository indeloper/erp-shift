<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
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
        DB::beginTransaction();

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 8,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 8,
                'permission_id' => $permissionTwo,
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

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionOne)->where('group_id', 8)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionTwo)->where('group_id', 8)->delete();

        DB::commit();
    }
};
