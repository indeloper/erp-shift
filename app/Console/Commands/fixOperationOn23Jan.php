<?php

namespace App\Console\Commands;

use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingMaterialAddition;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use Illuminate\Console\Command;

class fixOperationOn23Jan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:23_01';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create db rows';

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
        $part_127 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 127,
            'count' => 16.531,
            'unit' => 1,
            'type' => 9,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        MaterialAccountingMaterialAddition::create([
            'operation_id' => 238,
            'operation_material_id' => $part_127->id,
            'user_id' => 34,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $part_359206 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 359206,
            'count' => 1.537,
            'unit' => 1,
            'type' => 9,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        MaterialAccountingMaterialAddition::create([
            'operation_id' => 238,
            'operation_material_id' => $part_359206->id,
            'user_id' => 34,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $fact_359206 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 359206,
            'count' => 1.537,
            'unit' => 1,
            'type' => 2,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $fact_127 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 127,
            'count' => 16.531,
            'unit' => 1,
            'type' => 2,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $fact_23 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 23,
            'count' => 1.0248,
            'unit' => 1,
            'type' => 2,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $itog_359206 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 359206,
            'count' => 1.537,
            'unit' => 1,
            'type' => 4,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $itog_127 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 127,
            'count' => 16.531,
            'unit' => 1,
            'type' => 4,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $itog_23 = MaterialAccountingOperationMaterials::create([
            'operation_id' => 238,
            'manual_material_id' => 23,
            'count' => 1.0248,
            'unit' => 1,
            'type' => 4,
            'created_at' => '2020-01-22 22:13:17',
            'updated_at' => '2020-01-23 03:02:37',
        ]);

        $base107mat23 = MaterialAccountingBase::where('object_id', 107)->where('transferred_today', 0)->where('manual_material_id', 23)->first();
        $base107mat23->count += $itog_23->count;
        $base107mat23->save();

        $base107mat127 = MaterialAccountingBase::where('object_id', 107)->where('transferred_today', 0)->where('manual_material_id', 127)->first();
        $base107mat127->count += $itog_127->count;
        $base107mat127->save();

        MaterialAccountingBase::create([
            'object_id' => 107,
            'transferred_today' => 0,
            'manual_material_id' => 359206,
            'count' => 1.537,
            'date' => $base107mat23->date,
        ]);
    }
}
