<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')->insert([
            // defects
            [
                'category' => 15,
                'name' => 'Создание заявки о неисправности технического устройства',
                'codename' => 'tech_acc_defects_create',
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

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
            [
                'group_id' => 46,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 48,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

        DB::table('permissions')->where('codename', 'tech_acc_defects_create')->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
};
