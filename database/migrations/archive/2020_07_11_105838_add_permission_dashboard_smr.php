<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('permissions')->insert([
            // vehicle_categories
            [
                'category' => 1,
                'name' => 'Просмотр дашборда СМР',
                'codename' => 'dashboard_smr',
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', 'dashboard_smr')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 6,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 8,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::beginTransaction();

        $permissionId = DB::table('permissions')->where('codename', 'dashboard_smr')->first()->id;

        DB::table('permissions')->where('codename', 'dashboard_smr')->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
};
