<?php

use App\Models\q3wMaterial\q3wMaterialTransformationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*Schema::table('q3w_material_transformation_types', function($table) {
            $table->string('codename')->comment("Кодовое наименование");
        });*/

        /*$transformationType = new q3wMaterialTransformationType();
        $transformationType -> value = "Роспуск угловых";
        $transformationType -> codename = "CORNING_DISSOLUTION";
        $transformationType -> save();

        $transformationType = new q3wMaterialTransformationType();
        $transformationType -> value = "Спаривание балки";
        $transformationType -> codename = "BEAM_PAIR_PRODUCTION";
        $transformationType -> save();

        $transformationType = new q3wMaterialTransformationType();
        $transformationType -> value = "Роспуск спаренной балки";
        $transformationType -> codename = "BEAM_PAIR_DISSOLUTION";
        $transformationType -> save();

        $transformationType = q3wMaterialTransformationType::where("value", "like", "Резка")->first();
        $transformationType -> codename = "CUTTING";
        $transformationType -> save();

        $transformationType = q3wMaterialTransformationType::where("value", "like", "Стыковка по длине")->first();
        $transformationType -> codename = "LENGTH_DOCKING";
        $transformationType -> save();

        $transformationType = q3wMaterialTransformationType::where("value", "like", "Изготовление угловых")->first();
        $transformationType -> codename = "CORNING_PRODUCTION";
        $transformationType -> save();

        $transformationType = q3wMaterialTransformationType::where("value", "like", "Изготовление клиновидного")->first();
        $transformationType -> codename = "WEDGE_SHAPE_PRODUCTION";
        $transformationType -> save();*/

        /*Schema::table('q3w_material_transformation_types', function($table) {
            $table->string('codename')->unique()->string('codename')->comment("Кодовое наименование")->change();
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('q3w_material_transformation_types', function ($table) {
            $table->dropColumn('codename');
        });

        q3wMaterialTransformationType::where('value', 'like', 'Спаривание балки')->first()->forceDelete();
        q3wMaterialTransformationType::where('value', 'like', 'Роспуск угловых')->first()->forceDelete();
        q3wMaterialTransformationType::where('value', 'like', 'Роспуск спаренной балки')->first()->forceDelete();
    }
};
