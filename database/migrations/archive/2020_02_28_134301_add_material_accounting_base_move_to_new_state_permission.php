<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAME = 'mat_acc_base_move_to_new';

    const PERMISSION_NAME = 'Перевод материала с базы в состояние нового';

    public function up(): void
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
                'group_id' => 19,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
};
