<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOurVehicleStorePermission extends Migration
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
                "name" => 'Создание транспортного средства',
                "codename" => 'tech_acc_our_vehicle_create',
                'created_at' => now()
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_our_vehicle_create')->first()->id;

        DB::table('group_permissions')->insert([
            [
                'group_id' => 15,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 17,
                'permission_id' => $permissionId,
                'created_at' => now()
            ],
            [
                'group_id' => 47,
                'permission_id' => $permissionId,
                'created_at' => now()
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

        $permissionId = DB::table('permissions')->where('codename', 'tech_acc_our_vehicle_create')->first()->id;

        DB::table('permissions')->where('codename', 'tech_acc_vehicle_category_create')->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
}