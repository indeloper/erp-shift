<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMaterialAccountingBaseMoveToUsedStatePermission extends Migration
{
    const PERMISSION_CODENAME = 'mat_acc_base_move_to_used';

    const PERMISSION_NAME = 'Перевод материала с базы в Б/У состояние';

    public function up()
    {
        DB::beginTransaction();

        $insert = [];

        $insert[] = [
            'category' => 7,
            'name' => self::PERMISSION_NAME,
            'codename' => self::PERMISSION_CODENAME,
            'created_at' => now(),
        ];

        DB::table('permissions')->insert($insert);

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 13,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 14,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 19,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 23,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 31,
                'permission_id' => $permissionId,
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
        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
}
