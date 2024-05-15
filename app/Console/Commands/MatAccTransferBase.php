<?php

namespace App\Console\Commands;

use App\Models\MatAcc\MaterialAccountingBase;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatAccTransferBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mat_acc:transfer_base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer materials to next day';

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
     *
     * @return mixed
     */
    public function handle(): void
    {
        DB::beginTransaction();

        $bases = MaterialAccountingBase::with('object', 'material')->where('count', '>', 0.001)->where('transferred_today', 0)->where('date', '!=', Carbon::now()->format('d.m.Y'))->get();

        foreach ($bases as $item) {
            $base = new MaterialAccountingBase([
                'object_id' => $item->object_id,
                'manual_material_id' => $item->manual_material_id,
                'date' => Carbon::now()->format('d.m.Y'),
                'used' => $item->used,
                'ancestor_base_id' => $item->ancestor_base_id,
                'unit' => $item->unit,
                'count' => $item->count,
            ]);
            $base->save();
            if ($base->id != $item->id) {
                $item->copyCommentsTo($base);
            }
        }

        MaterialAccountingBase::where('transferred_today', 0)->where('date', '!=', Carbon::today()->format('d.m.Y'))->update(['transferred_today' => 1]);

        DB::commit();
    }
}
