<?php

namespace App\Services\MaterialAccounting\Reports;

use App\Models\MatAcc\MaterialAccountingOperation;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ObjectActionsExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct($object, $operations)
    {
        $this->object = $object;
        $this->operations = $operations;
    }

    public function title(): string
    {
        return 'Операции';
    }

    public function headings(): array
    {
        return [
            [
                'НАИМЕНОВАНИЕ ОБЪЕКТА',
            ],
            [
                $this->object->short_name ?? $this->object->address,
            ],
            [
                'Дата',
                'Вид работ',
                'Используемый материал',
                '',
                'шт.',
                'п.м./м^2',
                'тн.',
                'Доставка',
                'Отправка',
                'Примечание',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $firstColumn = 'A1:J1';
                $event->sheet->getDelegate()->mergeCells($firstColumn);

                $event->sheet->getStyle($firstColumn)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('70ad47');

                $event->sheet->horizontalAlign($firstColumn, \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $secondColumn = 'A2:J2';
                $event->sheet->getDelegate()->mergeCells($secondColumn);

                $event->sheet->horizontalAlign($secondColumn, \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $thirdColumn = 'A3:J3';

                $event->sheet->setAutoFilter($thirdColumn);
                $event->sheet->getStyle($thirdColumn)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('ffff00');

                $event->sheet->horizontalAlign('A:J', \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                foreach ($this->gradient('63be7b', 'ff5f76', $this->collection()->count()) as $index => $color) {
                    $event->sheet->getStyle('A'.($index + 4))
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB($color);
                }

            },
        ];
    }

    public function dateFrom(Carbon $date_from)
    {
        $this->date_from = $date_from;
    }

    public function dateTo(Carbon $date_to)
    {
        $this->date_to = $date_to;
    }

    public function collection()
    {
        $collection = collect();

        foreach ($this->operations as $key_oper => $operation) {
            $operation->load(['object_from', 'object_to']);
            foreach ($operation->materialsPart as $item) {
                unset($new_count_weith);
                $push = [];
                $push[0] = Carbon::parse($item->fact_date ?? $item->created_at)->format('d.m.Y');
                $push[1] = $this->getOperationName($operation, $item->type);
                $push[2] = $item->manual->name;
                $push[3] = '';

                if ($item->manual->category_unit == 'шт' && $item->units_name[$item->unit] != 'шт') {
                    $new_count_weith = round($item->manual->getConvertValueFromTo($item->units_name[$item->unit], 'шт') * $item->count, 3);
                    $push[4] = $new_count_weith;
                } elseif ($item->units_name[$item->unit] == 'шт') {
                    $new_count_weith = round($item->count, 3);
                    $push[4] = round($item->count, 3);
                } else {
                    $push[4] = '';
                }

                $push[5] = $item->units_name[$item->unit] == 'м.п' ? round($item->count, 3) : round(($item->manual()->first()->convert_from($item->units_name[$item->unit])->where('unit', 'м.п')->first()->value ?? 0) * (isset($new_count_weith) && $new_count_weith != 0 ? $new_count_weith : $item->count), 3);
                $push[6] = $item->units_name[$item->unit] == 'т' ? round($item->count, 3) : round(($item->manual->convert_from($item->units_name[$item->unit])->where('unit', 'т')->first()->value ?? 0) * $item->count, 3);
                $push[7] = $operation->object_to ? ($operation->object_to->short_name ?? $operation->object_to->name) : '';
                $push[8] = $operation->object_from ? ($operation->object_from->short_name ?? $operation->object_from->name) : '';
                $push[9] = $item->description;

                $need_push = ($operation->object_id_from == $this->object->id && $item->type == 8) || ($operation->object_id_to == $this->object->id && $item->type == 9);

                if ($need_push) {
                    $collection->push($push);
                }
            }
        }

        return $collection->sortBy(function ($item, $key) {
            return Carbon::parse($item[0])->diffInDays(Carbon::now()->startOfYear());
        });
    }

    public function gradient($HexFrom, $HexTo, $ColorSteps)
    {
        if ($ColorSteps == 1) {
            return ['63be7b'];
        }

        $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
        $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
        $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

        $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
        $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
        $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

        $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
        $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
        $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

        $GradientColors = [];

        for ($i = 0; $i <= $ColorSteps; $i++) {
            $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
            $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
            $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

            $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
            $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
            $HexRGB['b'] = sprintf('%02x', ($RGB['b']));

            $GradientColors[] = implode(null, $HexRGB);
        }

        $GradientColors = array_filter($GradientColors, function ($val) {
            return strlen($val) == 6 ? true : false;
        });

        return $GradientColors;
    }

    public function getOperationName(MaterialAccountingOperation $operation, $materialType)
    {
        if ($operation->type == 3) {
            return $materialType == 9 ? 'преобразование положительное' : 'преобразование отрицательное';
        } else {
            return $materialType == 9 ? 'завоз материала' : 'вывоз материала';
        }
    }
}
