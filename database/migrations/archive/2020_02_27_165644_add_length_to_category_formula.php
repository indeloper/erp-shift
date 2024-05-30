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

        $categories = ManualMaterialCategory::find([4, 6, 7, 8, 9, 11]);

        foreach ($categories as $category) {
            $attr_id = $category->attributes()->where('name', 'Длина')->first()->id;
            $category->update(['formula' => $category->formula.' '.'<attr>'.$attr_id.'</attr>'.' метров']);
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no way
    }
};
