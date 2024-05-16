<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameTechnicMovementCrudPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->where('codename', 'technics_movement_crud')
            ->update(['name' => 'Техника: перемещение - создание, редактирование, удаление']);

        DB::table('permissions')->where('codename', 'technics_movement_read')
            ->update(['name' => 'Техника: перемещение - просмотр всех записей']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
