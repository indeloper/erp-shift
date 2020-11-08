<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionToSeeFuelHistory extends Migration
{
    const PERMISSION_CODENAMES = [
        'see_fuel_operation_history',
    ];

    const PERMISSION_NAMES = [
        'Просмотр истории изменения операции по топливу',
    ];


    public function up()
    {
        $insert = [];

        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => 17,
                'name' => self::PERMISSION_NAMES[$key],
                'codename' => $codename,
                'created_at' => now()
            ];
        }

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 6,
                'permission_id' => $permissionOne,
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
        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionOne)->delete();
        DB::table('permissions')->whereIn('codename', self::PERMISSION_CODENAMES)->delete();

        DB::commit();
    }
}
