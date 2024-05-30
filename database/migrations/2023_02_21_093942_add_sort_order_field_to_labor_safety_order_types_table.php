<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('labor_safety_order_types', function (Blueprint $table) {
            $table->integer('sort_order')->unsigned()->after('name')->index();
        });

        $start = 10;
        $increment = 10;

        DB::table('labor_safety_order_types')->orderBy('id')->chunk(100, function ($orderTypes) use ($start, $increment) {
            foreach ($orderTypes as $orderType) {
                DB::table('labor_safety_order_types')
                    ->where('id', $orderType->id)
                    ->update(['sort_order' => $start]);

                $start += $increment;
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labor_safety_order_types', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
