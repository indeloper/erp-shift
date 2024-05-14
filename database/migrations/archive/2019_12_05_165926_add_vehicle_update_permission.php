<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddVehicleUpdatePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert([
            // vehicle_categories
            [
                'category' => 14,
                'name' => 'Обновление транспортного средства',
                'codename' => 'tech_acc_our_vehicle_update',
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_our_vehicle_update')->first()->id;

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
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_our_vehicle_update')->first()->id;

        DB::table('permissions')->where('codename', 'tech_acc_vehicle_category_update')->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
}
