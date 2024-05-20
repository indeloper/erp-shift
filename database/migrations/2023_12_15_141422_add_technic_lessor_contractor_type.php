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
        DB::table('contractor_types')->insert($this->getNewEntrises());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::NEW_ENTRIES as $newEntry) {
            DB::table('contractor_types')->where('slug', $newEntry['slug'])->delete();
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
            'name' => 'Арендодатель техники',
            'slug' => 'technic_lessor',
        ],
    ];
};
