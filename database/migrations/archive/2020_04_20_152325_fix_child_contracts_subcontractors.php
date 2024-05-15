<?php

use App\Models\Contract\Contract;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $child_contracts = Contract::where('type', 7)->get();
        foreach ($child_contracts as $child) {
            $child->subcontractor_id = $child->main_contract->subcontractor_id ?? $child->subcontractor_id;
            $child->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //nothing to do here, what's done is done
    }
};
