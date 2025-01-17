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
        DB::table('permissions')->insert([
            // vehicle_categories
            [
                'category' => 14,
                'name' => 'Изменение категории транспортных средств',
                'codename' => 'tech_acc_vehicle_category_edit',
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_vehicle_category_edit')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 15,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 17,
                'permission_id' => $permissionId,
                'created_at' => now(),
            ],
            [
                'group_id' => 47,
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

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_vehicle_category_edit')->first()->id;

        DB::table('permissions')->where('codename', 'tech_acc_vehicle_category_edit')->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
};
