<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewPermissonForProjectImportance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('permissions')->insert([
            [
                'id' => 47,
                'name' => 'Доступ к изменению важности проекта',
                'codename' => 'update_project_importance',
                'category' => 2,
                'created_at' => now()
            ]
        ]);

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => 47,
                'created_at' => now()
            ],
            [
                'group_id' => 6,
                'permission_id' => 47,
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
        DB::beginTransaction();

        DB::table('permissions')->where('id', 47)->delete();
        DB::table('group_permissions')->where('permission_id', 47)->delete();

        DB::commit();
    }
}