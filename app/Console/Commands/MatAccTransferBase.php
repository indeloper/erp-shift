<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingOperation;

use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

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
    public function handle()
    {
        DB::beginTransaction();

        $bases = MaterialAccountingBase::with('object', 'material')
            ->where('count', '>', 0.001)
            ->where('transferred_today', 0)
            ->where('date', '!=', Carbon::now()->format('d.m.Y'))
            ->orderByDesc('id')
            ->get();

        foreach ($bases as $item) {
            $base = MaterialAccountingBase::firstOrNew([
                'object_id' => $item->object_id,
                'manual_material_id' => $item->manual_material_id,
                'date' => Carbon::now()->format('d.m.Y'),
                'used' => $item->used,
            ]);
            $base->unit = $item->unit;
            $base->count += $item->count;
            $base->save();
        }

        MaterialAccountingBase::with('object', 'material')
            ->where('transferred_today', 0)
            ->where('date', '!=', Carbon::today()->format('d.m.Y'))
            ->update(['transferred_today' => 1]);

        DB::commit();
    }
}
