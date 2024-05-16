<?php

namespace App\Services\MaterialAccounting\Reports;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\ProjectObject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ObjectActionReportExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    public $operations;

    public $object;

    public function __construct(int $object_id)
    {
        $this->object = ProjectObject::findOrFail($object_id);
        $this->operations = MaterialAccountingOperation::query()
//            ->where('type', '!=', 3) // not transformation
            ->where('status', 3)
            ->where(function ($query) use ($object_id) {
                $query->where('object_id_to', $object_id);
                $query->orWhere('object_id_from', $object_id);
            })
            ->with('materialsPart.manual.convertation_parameters');
    }

    public function dateBetween($start_date, $end_date)
    {
        $start_date = $start_date ? Carbon::parse($start_date)->startOfDay() : Carbon::parse('01.01.2000')->startOfDay();
        $end_date = $start_date ? Carbon::parse($end_date)->endOfDay() : Carbon::parse('01.01.2100')->endOfDay();

        $this->operations = $this->operations->whereHas('materialsPart', function ($query) use ($start_date, $end_date) {
            $query->whereBetween('updated_at', [$start_date, $end_date]);
        });
    }

    public function sheets(): array
    {
        if (get_class($this->operations) == 'Illuminate\Database\Eloquent\Builder') {
            $this->operations = $this->operations->get();
        }
        $object = $this->object;
        $operations = $this->operations;

        $this->materials_planned = MaterialAccountingOperationMaterials::with('manual.convertation_parameters')
            ->with('operation', 'manual')
            ->whereHas('operation', function ($q) use ($object) {
                $q->where('object_id_to', $object->id);
            })
            ->whereIn('operation_id', $operations->pluck('id'))
            ->whereIn('type', [3])
            ->select('*', DB::raw('sum(count) as count'))
            ->groupBy('manual_material_id', 'type', 'unit')
            ->get();

        $this->materials_to = MaterialAccountingOperationMaterials::with('manual.convertation_parameters')
            ->with('operation', 'manual')
            ->whereHas('operation', function ($q) use ($object) {
                $q->where('object_id_to', $object->id);
            })
            ->whereIn('operation_id', $operations->pluck('id'))
            ->whereIn('type', [9])
            ->select('*', DB::raw('sum(count) as count'))
            ->groupBy('manual_material_id', 'type', 'unit')
            ->get();

        $this->materials_to_uniq = MaterialAccountingOperationMaterials::with('manual.convertation_parameters')
            ->with('operation', 'manual')
            ->whereHas('operation', function ($q) use ($object) {
                $q->where('object_id_to', $object->id);
            })
            ->whereIn('operation_id', $operations->pluck('id'))
            ->whereIn('type', [9])
            ->select('*', DB::raw('sum(count) as count'))
            ->groupBy('manual_material_id')
            ->get();

        $this->materials_from = MaterialAccountingOperationMaterials::with('manual.convertation_parameters')
            ->with('operation', 'manual')
            ->whereHas('operation', function ($q) use ($object) {
                $q->where('object_id_from', $object->id);
            })
            ->whereIn('operation_id', $operations->pluck('id'))
            ->whereIn('type', [8])
            ->select('*', DB::raw('sum(count) as count'))
            ->groupBy('manual_material_id', 'type', 'unit')
            ->get();

        $sheets = [];

        $sheets[] = new ObjectActionsExport($this->object, $this->operations);
        $sheets[] = new ObjectActionsMaterialsExport($this->object, $this->operations, $this->materials_planned, $this->materials_to, $this->materials_to_uniq, $this->materials_from);
        $sheets[] = new ObjectActionsMaterialsExport($this->object, $this->operations, $this->materials_planned, $this->materials_to, $this->materials_to_uniq, $this->materials_from, true);

        return $sheets;
    }

    public function export($fileName = 'report.xlsx')
    {
        return $this->download($fileName);
    }
}
