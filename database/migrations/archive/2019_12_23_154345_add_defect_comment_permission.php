<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_NAME = 'tech_acc_defect_comments_create';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')->insert([
            // defects
            [
                'category' => 15,
                'name' => 'Создание комментариев к заявке о неисправности технического устройства',
                'codename' => self::PERMISSION_NAME,
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_NAME)->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 6,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 13,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 19,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
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

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_NAME)->first()->id;

        DB::table('permissions')->where('codename', self::PERMISSION_NAME)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
};
