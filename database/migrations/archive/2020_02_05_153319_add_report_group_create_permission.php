<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAME = 'human_resources_report_group_create';

    const PERMISSION_NAME = 'Создание отчётной группы';

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
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
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
