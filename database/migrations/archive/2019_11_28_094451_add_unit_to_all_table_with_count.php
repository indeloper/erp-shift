<?php

use App\Models\CommercialOffer\CommercialOfferWork;
use App\Models\Manual\ManualNodeMaterials;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeWork;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitToAllTableWithCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commercial_offer_works', function (Blueprint $table) {
            $table->string('unit', 20)->default('шт');
        });

        Schema::table('manual_node_materials', function (Blueprint $table) {
            $table->string('unit', 20)->default('шт');
        });

        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->string('unit', 20)->default('шт');
        });

        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->string('unit', 20)->default('шт');
        });

        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->string('unit', 20)->default('шт');
        });

        $work_volume_work = WorkVolumeWork::with('manual')->get();
        foreach ($work_volume_work as $key => $item) {
            $item->update(['unit' => $item->manual->unit]);
            dump($item->id);
            dump('WorkVolumeWork: '.($key + 1).' in '.$work_volume_work->count());
        }
        dump('WorkVolumeWork done!');
        unset($work_volume_work);

        $max_id_wv_mat = WorkVolumeMaterial::max('id');
        WorkVolumeMaterial::query()->chunk(10, function ($work_volume_materials) use ($max_id_wv_mat) {
            foreach ($work_volume_materials as $key => $item) {
                if ($item->material_type == 'complect' || $item->manual->name == 'Объединённый материал') {
                    $unit = 'т';
                } else {
                    $unit = $item->manual->category->category_unit ?? 'т';
                }
                $item->update(['unit' => $unit]);

                dump('WorkVolumeMaterial: '.$item->id.' in '.$max_id_wv_mat);
            }
            sleep(0.5);
        });
        dump('WorkVolumeMaterial done!');

        $bases = MaterialAccountingBase::with('material')->get();
        foreach ($bases as $key => $item) {
            $item->update(['unit' => $item->material->category_unit]);
            dump('MaterialAccountingBase: '.($key + 1).' in '.$bases->count());
        }
        unset($bases);
        dump('MaterialAccountingBase done!');

        $com_offer_works = CommercialOfferWork::with('work_volume_parent.manual')->get();
        foreach ($com_offer_works as $key => $item) {
            $item->update(['unit' => $item->work_volume_parent->manual->unit]);
            dump('CommercialOfferWork: '.($key + 1).' in '.$com_offer_works->count());
        }
        unset($com_offer_works);
        dump('CommercialOfferWork done!');

        $node_materials = ManualNodeMaterials::with('materials.category')->get();
        foreach ($node_materials as $key => $item) {
            $item->update(['unit' => $item->materials->category->category_unit]);
            dump('ManualNodeMaterials: '.($key + 1).' in '.$node_materials->count());
        }
        dump('ManualNodeMaterials done!');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commercial_offer_works', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('manual_node_materials', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('work_volume_materials', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('work_volume_works', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
}
