<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAME = 'human_resources_timecard_fill';

    const PERMISSION_NAME = 'Заполнение табеля сотрудников';

    public function up(): void
    {
        $insert = [];

        $insert[] = [
            'category' => 18,
            'name' => self::PERMISSION_NAME,
            'codename' => self::PERMISSION_CODENAME,
            'created_at' => now(),
        ];

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        $permission = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 6,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 8,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 13,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 14,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 19,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 23,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 27,
                'permission_id' => $permission,
                'created_at' => now(),
            ],
            [
                'group_id' => 31,
                'permission_id' => $permission,
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
    public function down(): void
    {
        $permission = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permission)->delete();
        DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->delete();

        DB::commit();
    }
};
