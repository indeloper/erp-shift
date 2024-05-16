<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DefectResponsibleUserAssignmentPermission extends Migration
{
    const PERMISSION_NAME = 'tech_acc_defects_responsible_user_assignment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert([
            // defects
            [
                'category' => 15,
                'name' => 'Назначение исполнителя заявки о неисправности технического устройства',
                'codename' => self::PERMISSION_NAME,
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_NAME)->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 47,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_NAME)->first()->id;

        DB::table('permissions')->where('codename', self::PERMISSION_NAME)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
}
