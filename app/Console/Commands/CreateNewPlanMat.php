<?php

namespace App\Console\Commands;

use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingOperation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateNewPlanMat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mat_acc:new_plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating new plan material for moving operation: 6 and 7 instead of 3';

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
        DB::beginTransaction();

        $operations = MaterialAccountingOperation::where('type', 4)->whereIn('status', [1, 4, 5, 6, 8])->get();
        $total = $operations->count();
        $quater = $total / 4;
        $progress = 0;
        $this->info("we will iterate $total operations");
        $missed_operations = 0;
        foreach ($operations as $operation) {
            $progress++;
            if ($progress > $quater) {
                $this->info('25 proc done');
                $progress = 0;
            }
            $old_plans = $operation->materials()->where('type', 3)->get();
            foreach ($old_plans as $old_plan) {
                $plan_base_from = MaterialAccountingBase::where([
                    'object_id' => $operation->object_id_from,
                    'manual_material_id' => $old_plan->manual_material_id,
                    'used' => $old_plan->used,
                    'date' => $operation->actual_date_from ? $operation->actual_date_from : $operation->planned_date_from,
                ])->first();
                $plan_base_to = MaterialAccountingBase::where([
                    'object_id' => $operation->object_id_to,
                    'manual_material_id' => $old_plan->manual_material_id,
                    'used' => $old_plan->used,
                    'date' => $operation->actual_date_to ? $operation->actual_date_to : ($operation->planned_date_to ? $operation->planned_date_to : $operation->planned_date_from),
                ])->first();

                // if there is no base, we have to create one
                if (! $plan_base_from) {
                    $this->info("problem operation id is $operation->id");
                    if ($this->confirm('Do you wish to continue?')) {
                        $this->info("materials is $old_plan->material_name");
                        if ($this->confirm('create base?')) {
                            $plan_base_from = $this->createNewBase(false, $operation, $old_plan);
                        }
                    } else {
                        dd('stopped');
                    }
                }

                if (! $plan_base_to) {
                    $plan_base_to = $this->createNewBase(true, $operation, $old_plan);
                }

                $new_to = $old_plan->replicate();
                $new_to->type = 6;
                $new_to->base_id = $plan_base_to->id;
                $new_to->save();

                if ($plan_base_from) {
                    $old_plan->type = 7;
                    $old_plan->base_id = $plan_base_from->id;
                    $old_plan->save();
                }
            }
        }
        $this->info('done!');
        DB::commit();
    }

    private function createNewBase(int $is_to, $operation, $old_plan): MaterialAccountingBase
    {
        $date_to = $operation->actual_date_to ? $operation->actual_date_to : ($operation->planned_date_to ? $operation->planned_date_to : $operation->planned_date_from);
        $date_from = $operation->actual_date_from ? $operation->actual_date_from : $operation->planned_date_from;
        $plan_base = new MaterialAccountingBase([
            'object_id' => $is_to ? $operation->object_id_to : $operation->object_id_from,
            'manual_material_id' => $old_plan->manual_material_id,
            'used' => $old_plan->used,
            'date' => $is_to ? $date_to : $date_from,
            'count' => ! $is_to ? $old_plan->count : 0,
        ]);
        $plan_base->save();
        $plan_base->ancestor_base_id = $plan_base->id;
        $plan_base->save();

        return $plan_base;
    }
}
