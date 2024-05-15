<?php

use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->string('unit', 20)->default('шт');
        });

        $material_splits_count = CommercialOfferMaterialSplit::max('id');
        CommercialOfferMaterialSplit::query()->chunk(10, function ($com_offer_material_splits) use ($material_splits_count) {
            foreach ($com_offer_material_splits as $key => $item) {
                dump('current id:'.$item->id);
                $item->update(['unit' => $item->WV_material->manual->category_unit]);
                dump('CommercialOfferMaterialSplit: '.($item->id).' in '.$material_splits_count);
            }
        });
        dump('CommercialOfferMaterialSplit done!');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
