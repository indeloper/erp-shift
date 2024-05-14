<?php

namespace App\Console\Commands\Support\MaterialAccounting;

use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationMaterial;
use App\Models\q3wMaterial\q3wMaterial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelCompletedSupplyOperation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:material-accounting:cancel-supply-operation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels completed supply operation and restore object remains';

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
        $supplyOperationId = (int) $this->ask('Please, enter supply operation id');

        if (! $supplyOperationId) {
            $this->error('Operation id is required!');

            return;
        }

        $operation = q3wMaterialOperation::find($supplyOperationId);

        if (! $operation) {
            $this->error("Operation with id $supplyOperationId not found!");

            return;
        }

        if ($operation->operation_route_id !== 1) {
            $this->error("Operation with id $supplyOperationId is not supply operation!");

            return;
        }

        if ($operation->operation_route_stage_id !== 3) {
            $this->error("Operation with id $supplyOperationId is not yet completed or already cancelled!");

            return;
        }

        $operationMaterials = q3wOperationMaterial::where('material_operation_id', $supplyOperationId)
            ->leftJoin('q3w_material_standards', 'q3w_operation_materials.standard_id', 'q3w_material_standards.id')
            ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', 'q3w_material_types.id')
            ->leftJoin('q3w_operation_material_comments', 'q3w_operation_materials.comment_id', 'q3w_operation_material_comments.id')
            ->get(
                [
                    'q3w_operation_materials.id',
                    'q3w_operation_materials.standard_id',
                    'q3w_operation_materials.amount',
                    'q3w_operation_materials.quantity',
                    'q3w_operation_materials.edit_states',
                    'q3w_operation_materials.comment_id',
                    'q3w_operation_material_comments.comment',
                    'accounting_type',
                ]
            );

        DB::beginTransaction();

        foreach ($operationMaterials as $operationMaterial) {
            $objectMaterialQuery = q3wMaterial::where('project_object', $operation->destination_project_object_id)
                ->leftJoin('q3w_material_standards', 'q3w_materials.standard_id', 'q3w_material_standards.id')
                ->leftJoin('q3w_material_types', 'q3w_material_standards.material_type', 'q3w_material_types.id')
                ->leftJoin('q3w_material_comments', 'q3w_materials.comment_id', 'q3w_material_comments.id')
                ->where('q3w_materials.standard_id', $operationMaterial->standard_id);

            switch ($operationMaterial->accounting_type) {
                case 2:
                    $objectMaterialQuery->where('quantity', $operationMaterial->quantity);
                    break;
                default:
                    break;
            }

            if ($operationMaterial->comment_id) {
                $objectMaterialQuery->where('q3w_material_comments.comment', $operationMaterial->comment);
            } else {
                $objectMaterialQuery->whereNull('q3w_materials.comment_id');
            }

            $objectMaterial = $objectMaterialQuery->first(
                [
                    'q3w_materials.id',
                    'q3w_materials.standard_id',
                    'q3w_materials.quantity',
                    'q3w_materials.amount',
                    'q3w_materials.comment_id',
                    'q3w_material_comments.comment',
                    'q3w_material_types.accounting_type',
                ]
            );

            $this->line("Updating object material: $objectMaterial");

            switch ($objectMaterial->accounting_type) {
                case 2:
                    $objectMaterial->amount -= $operationMaterial->amount;

                    if ($objectMaterial->amount < 0) {
                        $this->error("Amount of material on object less than 0 ($objectMaterial->amount). Rolling back.");
                        DB::rollBack();

                        return;
                    } else {
                        $objectMaterial->save();
                        $this->line("Updated. New amount - $objectMaterial->amount\n");
                    }
                    break;
                default:
                    $objectMaterial->quantity -= round($operationMaterial->amount * $operationMaterial->quantity, 2);

                    if ($objectMaterial->quantity < 0) {
                        $this->error("Quantity of material on object less than 0 ($objectMaterial->quantity). Rolling back.");
                        DB::rollBack();

                        return;
                    } else {
                        $objectMaterial->save();
                        $this->line("Updated. New quantity - $objectMaterial->quantity\n");
                    }
                    break;
            }
        }

        $operation->operation_route_stage_id = 83;
        $operation->save();

        if ($this->confirm('Commit changes?')) {
            DB::commit();
            $this->line('Operation successfully cancelled');
        } else {
            DB::rollback();
            $this->line('Rolled back');
        }

    }
}
