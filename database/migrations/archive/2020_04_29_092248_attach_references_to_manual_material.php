<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualReference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AttachReferencesToManualMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $categories = ManualMaterialCategory::whereNotIn('id', [12, 14])->get();

        foreach ($categories as $category) {
            $references = ManualReference::where('category_id', $category->id)->get();

            foreach ($references as $reference) {
                $materials = ManualMaterial::where('category_id', $category->id)->where('name', 'like', $reference->name.'%')->get();

                foreach ($materials as $index => $material) {
                    $material->manual_reference_id = $reference->id;
                    $material->save();
                }
            }
            //            dump($references->count());
            //            dump($category->name . ' done!');
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ManualMaterial::query()->update(['manual_reference_id' => 0]);
    }
}
