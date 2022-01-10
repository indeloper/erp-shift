<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddObjectTimeResponsibleUserChangePermission extends Migration
{
    const PERMISSION_CODENAME = 'human_resources_project_time_responsible_user_change';

    const PERMISSION_NAME = 'Изменение ответственного за учёт рабочего времени';

    public function up()
    {
        $insert = [];

        $insert[] = [
            'category' => 18,
            "name" => self::PERMISSION_NAME,
            "codename" => self::PERMISSION_CODENAME,
            'created_at' => now()
        ];

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        $permission = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => $permission,
                'created_at' => now()
            ],
            [
                'group_id' => 6,
                'permission_id' => $permission,
                'created_at' => now()
            ],
            [
                'group_id' => 8,
                'permission_id' => $permission,
                'created_at' => now()
            ],
            [
                'group_id' => 13,
                'permission_id' => $permission,
                'created_at' => now()
            ],
            [
                'group_id' => 19,
                'permission_id' => $permission,
                'created_at' => now()
            ],
            [
                'group_id' => 27,
                'permission_id' => $permission,
                'created_at' => now()
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
        $permission = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permission)->delete();
        DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->delete();

        DB::commit();
    }
}
