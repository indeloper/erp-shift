<?php

namespace App\Services\MaterialAccounting;

use App\Models\MatAcc\MaterialAccountingOperation;
use Illuminate\Support\Facades\DB;

class MaterialAccountingBadMaterilas
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $operations;

    public function __construct()
    {
        $this->operations = MaterialAccountingOperation::query()
            ->where('type', 4) // not transformation
//            ->where('status', '!=', 7)
            ->where('status', 3)

            ->whereDate('created_at', '>', '2020-04-15')
            ->whereHas('materialsPart')
            ->with('materialsPart.manual.convertation_parameters')->get();
    }

    public function check()
    {
        $bad_operations = [];
        foreach ($this->operations as $operation) {
            $materials_from = $operation
                ->materials()
                ->select(DB::raw('*, sum(count) as sum_count'))
                ->where('type', 1)
                ->groupBy('manual_material_id')
                ->get()
                ->pluck('sum_count', 'manual_material_id');
            $materials_to = $operation
                ->materials()
                ->select(DB::raw('*, sum(count) as sum_count'))
                ->where('type', 2)
                ->groupBy('manual_material_id')
                ->get()
                ->pluck('sum_count', 'manual_material_id');

            foreach ($materials_from as $manual_material_id => $count) {
                if (! isset($materials_to[$manual_material_id]) || $materials_to[$manual_material_id] != $count) {
                    $bad_operations[] = $operation->id;
                    break;
                }
            }
        }
        dd($bad_operations);
    }
}
