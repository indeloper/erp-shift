<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const PERMISSION_HUMAN_NAME = 'Создание заявки на использование и/или перемещение техники';

    const PERMISSION_NAME = 'create.OurTechnicTicket';

    const PERMISSION_CATEGORY_ID = 16;

    const PERMISSION_GROUP_IDS = [8, 13, 19, 27, 14, 23, 31];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert([
            // defects
            [
                'category' => self::PERMISSION_CATEGORY_ID,
                'name' => self::PERMISSION_HUMAN_NAME,
                'codename' => self::PERMISSION_NAME,
                'created_at' => now(),
            ],
        ]);

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_NAME)->first()->id;

        foreach (self::PERMISSION_GROUP_IDS as $GROUP_ID) {
            DB::table('group_permissions')->insert([
                [
                    'group_id' => $GROUP_ID,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                ],
            ]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        $permissionId = DB::table('permissions')->where('codename', self::PERMISSION_NAME)->first()->id;

        DB::table('permissions')->where('codename', self::PERMISSION_NAME)->delete();
        DB::table('group_permissions')->where('permission_id', $permissionId)->delete();

        DB::commit();
    }
};
