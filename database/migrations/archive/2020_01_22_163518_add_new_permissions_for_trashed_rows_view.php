<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_CODENAMES = [
        'tech_acc_vehicle_categories_trashed',
        'tech_acc_vehicles_trashed',
        'tech_acc_tech_categories_trashed',
        'tech_acc_techs_trashed',
        'tech_acc_fuel_tanks_trashed',
    ];

    const PERMISSION_NAMES = [
        'Просмотр удаленных категорий транспорта',
        'Просмотр удаленных транспортных средств',
        'Просмотр удаленных категорий техники',
        'Просмотр удаленных технических средств',
        'Просмотр удаленных топливных ёмкостей',
    ];

    const PERMISSION_GROUPS = [14, 14, 13, 13, 17];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $insert = [];

        foreach (self::PERMISSION_CODENAMES as $key => $codename) {
            $insert[] = [
                'category' => self::PERMISSION_GROUPS[$key],
                'name' => self::PERMISSION_NAMES[$key],
                'codename' => $codename,
                'created_at' => now(),
            ];
        }

        DB::beginTransaction();

        DB::table('permissions')->insert($insert);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        Permission::whereIn('codename', self::PERMISSION_CODENAMES)->delete();

        DB::commit();
    }
};
