<?php

namespace App\Console\Commands;

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MoveManualRodTo7Category extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rod:move';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves materials, reference and parameters from one category to another';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $old_category = 8;
        $target_category = 7;
        //from cat 8 to cat 7
        $old_mats = ManualMaterial::where('category_id', $old_category)->get();

        DB::beginTransaction();
        //parameters
        foreach ($old_mats as $old_mat) { //transfer material parameters to another cat
            foreach ($old_mat->parameters as $parameter) {
                $old_attr = $parameter->attribute;
                $new_attr = ManualMaterialCategoryAttribute::where('category_id', $target_category)->where('name', $old_attr->name)->first();
                if ($new_attr) { //we will lost attrs that differ from new one
                    $parameter->attr_id = $new_attr->id;
                    $parameter->save();
                }
            }
            //manual
            $old_mat->category_id = $target_category;
            $old_mat->push();
        }
        //reference
        ManualReference::where('category_id', $old_category)->update(['category_id' => $target_category]);

        ManualMaterialCategory::where('id', $old_category)->delete();

        DB::commit();

        $this->info('success');
    }
}
