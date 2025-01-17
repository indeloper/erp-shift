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
        DB::beginTransaction();

        DB::table('permissions')->insert([
            [
                'id' => 48,
                'name' => 'Доступ к ЭЦП',
                'codename' => 'work_with_digital_signature',
                'category' => 9,
                'created_at' => now(),
            ],
        ]);

        DB::table('group_permissions')->insert([
            [
                'group_id' => 5,
                'permission_id' => 48,
                'created_at' => now(),
            ],
            [
                'group_id' => 6,
                'permission_id' => 48,
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
        DB::beginTransaction();

        DB::table('permissions')->where('id', 48)->delete();
        DB::table('group_permissions')->where('permission_id', 48)->delete();

        DB::commit();
    }
};
