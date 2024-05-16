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
        DB::table('permissions')->where('codename', 'technics_movement_crud')
            ->update(['name' => 'Техника: перемещение - создание, редактирование, удаление']);

        DB::table('permissions')->where('codename', 'technics_movement_read')
            ->update(['name' => 'Техника: перемещение - просмотр всех записей']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
