<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAMES = [
        'tech_acc_our_technic_tickets_see',
        'tech_acc_defects_see',
    ];

    public function up(): void
    {
        DB::beginTransaction();

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 13,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 13,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'group_id' => 19,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 19,
                'permission_id' => $permissionTwo,
                'created_at' => now(),
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionOne,
                'created_at' => now(),
            ],
            [
                'group_id' => 27,
                'permission_id' => $permissionTwo,
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
        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[0])->first()->id;
        $permissionTwo = DB::table('permissions')->where('codename', self::PERMISSION_CODENAMES[1])->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->whereIn('permission_id', [$permissionOne, $permissionTwo])->whereIn('group_id', [13, 19, 27])->delete();

        DB::commit();
    }
};
