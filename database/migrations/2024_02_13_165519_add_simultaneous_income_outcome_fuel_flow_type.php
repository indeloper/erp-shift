<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSimultaneousIncomeOutcomeFuelFlowType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('fuel_tank_flow_types')->insert($this->getNewEntries());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach(self::NEW_ENTRIES as $newEntry) {
            DB::table('fuel_tank_flow_types')->where('slug', $newEntry['slug'])->delete();
        }
    }

    public function getNewEntries()
    {
        $newEntries = self::NEW_ENTRIES;
        foreach($newEntries as $key=>$newEntry) {
            $newEntries[$key]['created_at'] = now();
            $newEntries[$key]['updated_at'] = now();
        }
        return $newEntries;
    }

    const NEW_ENTRIES = [
        [
            'name' => 'Прямая заправка', 
            'slug' => 'simultaneous_income_outcome',
        ],
    ];

}
