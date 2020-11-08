<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewGroupsInSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('groups')->insert([
            // smb from Шпунтовове направление
            ['id' => 56, 'name' => 'Машинист экскаватора', 'department_id' => 11],
            // АХО department
            ['id' => 57, 'name' => 'Заместитель генерального директора', 'department_id' => 1],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        DB::table('groups')->where('name', 'Машинист экскаватора')->delete();
        DB::table('groups')->where('name', 'Заместитель генерального директора')->delete();

        DB::commit();
    }
}
