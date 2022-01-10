<?php

namespace App\Services\MaterialAccounting\Reports;

use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;
use App\Models\MatAcc\MaterialAccountingBase;

use App\Models\ProjectObject;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
Use \Maatwebsite\Excel\Sheet;
use \Maatwebsite\Excel\Writer;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class ObjectActionsMaterialsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle{

    use Exportable;

    /**
     * @var MaterialAccountingOperationMaterials[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public $materials_to;
    /**
     * @var MaterialAccountingOperationMaterials[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public $materials_from;
    /**
     * @var MaterialAccountingOperationMaterials[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public $materials_planned;

    public function __construct($object, $operations, $materials_planned, $materials_to, $materials_to_uniq, $materials_from,  $balance = false)
    {
        $this->object = $object;
        if ($balance) {
            $this->mats_balance = MaterialAccountingBase::with('material.convertation_parameters')
                ->where('object_id', $object->id)
                ->where('date', Carbon::today()->format('d.m.Y'))
                ->get();
        }

        $this->operations = $operations;
        $this->materials_planned = $materials_planned;

        $this->materials_to = $materials_to;

        $this->materials_to_uniq = $materials_to_uniq;

        $this->materials_from = $materials_from;

        $this->balance = $balance;
    }

    public function title(): string
    {
        return $this->balance ? 'Остаток' :'Материалы';
    }

    public function headings(): array
    {
        return [
            [
                $this->object->short_name ?? $this->object->address
            ],
            [
                'Марка поз.',
                'Обозначение',
                'Наименование',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Масса ед.,кг.',
                'Примечание',
            ],
            [
                '',
                '',
                '',
                $this->balance ? 'Остаток' : 'Планируемое',
                '',
                '',
                'Завезено',
                '',
                '',
                'Вывезено',
                '',
                '',
            ],
            [
                '',
                '',
                '',
                'шт',
                'п.м./м^2',
                $this->balance ? '' : 'тн.',
                'шт.',
                'п.м./м^2',
                'тн.',
                'шт.',
                'п.м./м^2',
                'тн.',
                '',
                '',
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $firstColumn = 'A1:N1';
                $event->sheet->getDelegate()->mergeCells($firstColumn);

                $event->sheet->getStyle($firstColumn)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('ffbb04');

                $event->sheet->horizontalAlign($firstColumn , \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $columnA = 'A2:A4';
                $event->sheet->getDelegate()->mergeCells($columnA);

                $columnB = 'B2:B4';
                $event->sheet->getDelegate()->mergeCells($columnB);

                $columnC= 'C2:C4';
                $event->sheet->getDelegate()->mergeCells($columnC);

                $columnM= 'M2:M4';
                $event->sheet->getDelegate()->mergeCells($columnM);

                $columnN= 'N2:N4';
                $event->sheet->getDelegate()->mergeCells($columnN);


                $rowEF2 = 'D2:L2';
                $event->sheet->getDelegate()->mergeCells($rowEF2);

                $rowDF3 = 'D3:F3';
                $event->sheet->getDelegate()->mergeCells($rowDF3);

                $rowGI3 = 'G3:I3';
                $event->sheet->getDelegate()->mergeCells($rowGI3);

                $rowJL3 = 'J3:L3';
                $event->sheet->getDelegate()->mergeCells($rowJL3);

                $fifthColumn = 'A5:N5';
                $event->sheet->getDelegate()->mergeCells($fifthColumn);

                $event->sheet->getStyle($fifthColumn)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('bdd7ee');

                $event->sheet->getStyle('C6:C' . ($this->getMaterialsName()->count() + 4))
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('bfbfbf');

                $event->sheet->horizontalAlign('A:N' , \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->verticalAlign('A:N' , \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            }
        ];
    }

    public function collection()
    {
        $collection = [];
        $collection[0] = [[]];
        // $materialNames = $this->getMaterialsName();
        $all_materials_planned = $this->materials_planned; // type 3, 6, 7
        $all_materials_to = $this->materials_to; // type 9
        $all_materials_from = $this->materials_from;// type 8
        $materials_to_uniq = $this->materials_to_uniq;
        foreach ($materials_to_uniq as $item) {
            unset($new_count_weith);
            $mat_to = $all_materials_to->where('manual_material_id', $item->manual_material_id);
            $mat_from = $all_materials_from->where('manual_material_id', $item->manual_material_id);
            $planned_mat = $all_materials_planned->where('manual_material_id', $item->manual_material_id);
            if ($this->balance) {
                $balance_mat = $this->mats_balance->where('manual_material_id', $item->manual_material_id);
            }

            $push = [];
            $push[0] = '';
            $push[1] = '';
            $push[2] = $item->manual->name;
            // fork for balance


            if ($this->balance && $balance_mat) {
                $push[3] = 0;
                $push[4] = 0;
                $push[5] = 0;
                foreach ($balance_mat as $material) {
                    $push[3] += (isset($material) && $material->unit == 'шт') ? round($material->count, 3) : round(($material->material->convert_from($material->unit)->where('unit', 'шт')->first()->value ?? 0) * ($material->count), 3);
                    $push[4] += (isset($material) && $material->unit == 'м.п') ? round($material->count, 3) : round(($material->material->convert_from($material->unit)->where('unit', 'м.п')->first()->value ?? 0) * ($material->count), 3);
                    $push[5] += (isset($material) && $material->unit == 'т') ? round($material->count, 3) : round(($material->material->convert_from($material->unit)->where('unit', 'т')->first()->value ?? 0) * $material->count, 3);
                }
            }
            else {
                $push[3] = '';
                $push[4] = '';
                $push[5] = '';
            }

            $push[6] = 0;
            $push[7] = 0;
            $push[8] = 0;
            foreach ($mat_to as $material) {
                $push[6] += $material->units_name[$material->unit] == 'шт' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'шт')->first()->value ?? 0) * ($material->count), 3);
                $push[7] += $material->units_name[$material->unit] == 'м.п' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'м.п')->first()->value ?? 0) * ($material->count), 3);
                $push[8] += $material->units_name[$material->unit] == 'т' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'т')->first()->value ?? 0) * $material->count, 3);
            }

            if ($mat_from) {
                $push[9] = 0;
                $push[10] = 0;
                $push[11] = 0;
                foreach ($mat_from as $material) {
                    $push[9] += $material->units_name[$material->unit] == 'шт' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'шт')->first()->value ?? 0) * ($material->count), 3);
                    $push[10] += $material->units_name[$material->unit] == 'м.п' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'м.п')->first()->value ?? 0) * ($material->count), 3);
                    $push[11] += $material->units_name[$material->unit] == 'т' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'т')->first()->value ?? 0) * $material->count, 3);
                }
            }
            else {
                $push[9] = '';
                $push[10] = '';
                $push[11] = '';
            }

            if (!$this->balance) {
                if ($planned_mat){
                    $push[3] = 0;
                    $push[4] = 0;
                    $push[5] = 0;
                    foreach ($planned_mat as $material) {
                        $push[3] += $material->units_name[$material->unit] == 'шт' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'шт')->first()->value ?? 0) * ($material->count), 3);
                        $push[4] += $material->units_name[$material->unit] == 'м.п' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'м.п')->first()->value ?? 0) * ($material->count), 3);
                        $push[5] += $material->units_name[$material->unit] == 'т' ? round($material->count, 3) : round(($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'т')->first()->value ?? 0) * $material->count, 3);
                    }
                }
                else {
                    $push[3] = '';
                    $push[4] = '';
                    $push[5] = '';
                }
            }

            if (isset($item->manual->convert_from($item->units_name[$item->unit])->where('unit', 'м.п')->first()->value)) {
                if ($item->manual->convert_from($item->units_name[$item->unit])->where('unit', 'м.п')->first()->value) {
                    $push[12] = round(1 / $item->manual->convert_from($item->units_name[$item->unit])->where('unit', 'м.п')->first()->value, 3);
                }
            } else {
                $push[13] = '';
            }
            $push[13] = '';

            foreach ($collection as $index => $item) {
                if (isset($item[2]) && $item[2] == $push[2]) {
                    foreach ([6, 7, 8, 9, 10, 11] as $number) {
                        if (is_numeric($push[$number]) && is_numeric($push[$number])) {
                            $collection[$index][$number] += $push[$number];
                        }
                    }
                    break;
                } elseif (count($collection) - 1 == $index) {
                    $collection[] = $push;
                    break;
                }
            }

        }

        return collect($collection);
    }

    public function getMaterialsName()
    {
        $materialNames = collect();
        foreach ($this->operations as $operation) {
            foreach ($operation->materialsPart as $item) {
                $materialNames->push($item->manual->name);
            }
        }

        $materialNames = $materialNames->unique();

        return $materialNames;
    }

    public function export($fileName = 'report.xlsx')
    {
        return $this->download($fileName);
    }
}
