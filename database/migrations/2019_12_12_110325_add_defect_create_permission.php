<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefectCreatePermission extends Migration
{
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
                "name" => 'Создание заявки о неисправности технического устройства',
                "codename" => 'tech_acc_defects_create',
                'created_at' => now()
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 13,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 14,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 19,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 23,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 31,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 46,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 48,
                'permission_id' => $permissionId,
                'created_at' => now()
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

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

        DB::table('permissions')->where('codename', 'tech_acc_defects_create')->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
}
