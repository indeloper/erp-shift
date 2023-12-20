<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefectCreatePermissionForPrincipalEngineer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 8,
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

        DB::table('group_permissions')->where('permission_id', $permissionId)->where('group_id', 8)->delete();

        DB::commit();
    }
}
