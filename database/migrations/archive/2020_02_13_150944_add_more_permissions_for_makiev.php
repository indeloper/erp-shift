<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAME = 'tech_acc_our_technic_tickets_see';

    const PERMISSION_NAME = 'Просмотр всех заявок на технику';

    public function up(): void
    {
        DB::beginTransaction();

        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 35,
                'permission_id' => $permissionOne,
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
        $permissionOne = DB::table('permissions')->where('codename', self::PERMISSION_CODENAME)->first()->id;

        DB::beginTransaction();

        DB::table('group_permissions')->where('permission_id', $permissionOne)->where('group_id', 35)->delete();

        DB::commit();
    }
};
