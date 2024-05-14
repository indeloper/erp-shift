<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTechAccountingTechnicCategoryPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert([
            // tasks
            [
                'category' => 13,
                'name' => 'Создание категории техники',
                'codename' => 'tech_acc_tech_category_create',
                'created_at' => now(),
            ],
            [
                'category' => 13,
                'name' => 'Редактирование категории техники',
                'codename' => 'tech_acc_tech_category_update',
                'created_at' => now(),
            ],
            [
                'category' => 13,
                'name' => 'Удаление категории техники',
                'codename' => 'tech_acc_tech_category_delete',
                'created_at' => now(),
            ],
        ]);

        $permissionOne = DB::table('permissions')->where('codename', 'tech_acc_tech_category_create')->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', 'tech_acc_tech_category_update')->first()->id;
        $permissionThree = DB::table('permissions')->where('codename', 'tech_acc_tech_category_delete')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 15,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 15,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'group_id' => 15,
                'permission_id' => $permissionThree,
                'created_at' => now(),
            ],
            [
                'group_id' => 17,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 17,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'group_id' => 17,
                'permission_id' => $permissionThree,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionThree,
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
        $permissionOne = DB::table('permissions')->where('codename', 'tech_acc_tech_category_create')->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', 'tech_acc_tech_category_update')->first()->id;
        $permissionThree = DB::table('permissions')->where('codename', 'tech_acc_tech_category_delete')->first()->id;

        DB::beginTransaction();

        DB::table('permissions')->where('category', 13)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionOne)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionTwo)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionThree)->delete();

        DB::commit();
    }
}
