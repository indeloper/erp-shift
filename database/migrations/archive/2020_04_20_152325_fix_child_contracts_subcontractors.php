<?php

use App\Models\Contract\Contract;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixChildContractsSubcontractors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $child_contracts = Contract::where('type', 7)->get();
        foreach($child_contracts as $child) {
            $child->subcontractor_id = $child->main_contract->subcontractor_id ?? $child->subcontractor_id;
            $child->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //nothing to do here, what's done is done
    }
}
