<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAMES = [
        'materials_remove',
        'works_remove',
        'material_categories_remove',
        'work_categories_remove',
    ];

    const PERMISSION_NAMES = [
        'Удаление материалов',
        'Удаление работ',
        'Удаление категорий материалов',
        'Удаление категорий работ',
    ];

    public function up()
    {
        DB::beginTransaction();

        $insert = [];

        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => $key % 2 === 0 ? 5 : 6,
                'name' => self::PERMISSION_NAMES[$key],
                'codename' => $codename,
                'created_at' => now(),
            ];
        }

        DB::table('permissions')->insert($insert);

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;
        $permissionThree = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[2])->first()->id;
        $permissionFour = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[3])->first()->id;

        DB::table('user_permissions')->insert([
            [
                'user_id' => 65,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'user_id' => 65,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'user_id' => 65,
                'permission_id' => $permissionThree,
                'created_at' => now(),
            ],
            [
                'user_id' => 65,
                'permission_id' => $permissionFour,
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
    public function down()
    {
        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;
        $permissionThree = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[2])->first()->id;
        $permissionFour = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[3])->first()->id;

        DB::beginTransaction();

        DB::table('user_permissions')->where('permission_id', $permissionOne)->delete();
        DB::table('user_permissions')->where('permission_id', $permissionTwo)->delete();
        DB::table('user_permissions')->where('permission_id', $permissionThree)->delete();
        DB::table('user_permissions')->where('permission_id', $permissionFour)->delete();

        DB::commit();
    }
};
