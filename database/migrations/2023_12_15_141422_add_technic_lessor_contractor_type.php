<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTechnicLessorContractorType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('contractor_types')->insert($this->getNewEntrises());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 
        foreach(self::NEW_ENTRIES as $newEntry) {
            DB::table('contractor_types')->where('slug', $newEntry['slug'])->delete();
        }
    }

    public function getNewEntrises() {
        $newEntries = self::NEW_ENTRIES;
        foreach($newEntries as $key=>$newEntry) {
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
}