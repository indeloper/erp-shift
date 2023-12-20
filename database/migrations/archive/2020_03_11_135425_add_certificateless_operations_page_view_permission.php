<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddCertificatelessOperationsPageViewPermission extends Migration
{
    const PERMISSION_CODENAMES = [
        'see_certificateless_operations',
    ];

    const PERMISSION_NAMES = [
        'Просмотр страницы операций без сертификатов',
    ];


    public function up()
    {
        $insert = [];

        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => 7,
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
            [
                'group_id' => 8
,
                'permission_id' => $permissionOne,
                'created_at' => now()],
            [
                'group_id' => 13,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 14,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 15,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 17,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 19,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 23,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 31,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 35,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 39,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 40,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 41,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 42,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 43,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 44,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 45,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 46,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 48,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 52,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 53,
                'permission_id' => $permissionOne,
                'created_at' => now()
            ],
            [
                'group_id' => 54,
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
