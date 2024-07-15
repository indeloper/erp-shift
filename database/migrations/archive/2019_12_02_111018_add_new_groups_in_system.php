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
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('groups')->where('name', 'Машинист экскаватора')->delete();
        DB::table('groups')->where('name', 'Заместитель генерального директора')->delete();

        DB::commit();
    }
};
