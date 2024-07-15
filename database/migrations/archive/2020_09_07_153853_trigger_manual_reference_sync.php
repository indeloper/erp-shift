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
        \App\Models\Manual\ManualReference::all()->each->save();
        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
