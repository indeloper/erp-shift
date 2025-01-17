<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 8,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_defects_create')->first()->id;

        DB::table('group_permissions')->where('permission_id', $permissionId)->where('group_id', 8)->delete();

        DB::commit();
    }
};
