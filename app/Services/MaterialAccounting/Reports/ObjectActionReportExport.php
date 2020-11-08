<?php

namespace App\Services\MaterialAccounting\Reports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\ProjectObject;

use Carbon\Carbon;

class ObjectActionReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(int $object_id)
    {
        $this->object = ProjectObject::findOrFail($object_id);
        $this->operations = MaterialAccountingOperation::query()
//            ->where('type', '!=', 3) // not transformation
            ->where('status', 3)
        ->where(function($query) use ($object_id) {
            $query->where('object_id_to', $object_id);
            $query->orWhere('object_id_from', $object_id);
        })
        ->with('materialsPart.manual.convertation_parameters');
    }

    public function dateBetween($start_date, $end_date)
    {
        $start_date = $start_date ? Carbon::parse($start_date)->startOfDay() : Carbon::parse('01.01.2000')->startOfDay();
        $end_date = $start_date ? Carbon::parse($end_date)->endOfDay() : Carbon::parse('01.01.2100')->endOfDay();

        $this->operations = $this->operations->whereHas('materialsPart', function ($query) use($start_date, $end_date) {
            $query->whereBetween('updated_at', [$start_date, $end_date]);
        });
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        // $this->operations = $this->operations->get();
        $sheets = [];

        $sheets[] = new ObjectActionsExport($this->object, $this->operations);
        $sheets[] = new ObjectActionsMaterialsExport($this->object, $this->operations);
        $sheets[] = new ObjectActionsMaterialsExport($this->object, $this->operations, true);


        return $sheets;
    }


    public function export($fileName = 'report.xlsx')
    {
        return $this->download($fileName);
    }
}
