<?php

use App\Models\Manual\ManualMaterialCategory;
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

        $category = ManualMaterialCategory::find(5);
        $category->load('attributes');
        $attr = $category->attributes()->where('name', 'Толщина')->first();
        $attr->step = 1;
        $attr->save();

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(5);
        $category->load('attributes');
        $attr = $category->attributes()->where('name', 'Толщина')->first();
        $attr->step = 2;
        $attr->save();

        DB::commit();
    }
};
