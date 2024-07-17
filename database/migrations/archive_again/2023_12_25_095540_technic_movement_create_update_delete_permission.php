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
        DB::table('permissions')->insert($this->getNewEntrises());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::NEW_ENTRIES as $newEntry) {
            DB::table('permissions')->where('codename', $newEntry['codename'])->delete();
        }
    }

    public function getNewEntrises()
    {
        $newEntries = self::NEW_ENTRIES;
        foreach ($newEntries as $key => $newEntry) {
            $newEntries[$key]['created_at'] = now();
            $newEntries[$key]['updated_at'] = now();
        }

        return $newEntries;
    }

    const NEW_ENTRIES = [
        [
            'name' => 'Топливные емкости: перемещение техники - создание, редактирование, удаление',
            'codename' => 'technics_movement_crud',
            'category' => 13,
        ],
        [
            'name' => 'Топливные емкости: перемещение техники - просмотр',
            'codename' => 'technics_movement_read',
            'category' => 13,
        ],
    ];
};
