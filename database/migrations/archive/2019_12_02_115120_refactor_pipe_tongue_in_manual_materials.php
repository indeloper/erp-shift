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

        $category_tongue = ManualMaterialCategory::find(2);

        $attr_mark = $category_tongue->attributes()->where('name', 'like', '%'.'марка'.'%')->first();

        foreach ($category_tongue->materials()->where('name', 'like', '%Трубошпунт%')->get() as $material) {
            $mark = '';
            $explode = explode(' ', $material->name);
            array_pop($explode);
            array_pop($explode);
            array_shift($explode);

            $mark = implode(' ', $explode);
            $material->parameters()->create([
                'attr_id' => $attr_mark->id,
                'value' => $mark,
            ]);
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        $category_tongue = ManualMaterialCategory::find(2);

        $attr_mark = $category_tongue->attributes()->where('name', 'like', '%'.'марка'.'%')->first();

        foreach ($category_tongue->materials()->where('name', 'like', '%Трубошпунт%')->get() as $material) {
            $material->parameters()->where('attr_id', $attr_mark->id)->delete();
        }

        DB::commit();
    }
};
