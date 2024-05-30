<?php

namespace App\Services\MaterialAccounting\Reports;

use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualReference;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class BasesReportExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithDrawings, WithEvents, WithHeadings, WithTitle
{
    use Exportable;

    /**
     * @var array
     */
    private $date;

    private $request;

    /**
     * @var Collection
     */
    private $materials;

    /**
     * @var int
     */
    private $count;

    private $objects;

    private $count_convert;

    /**
     * {@inheritDoc}
     */
    public function __construct($data, $customRequest)
    {
        $this->count_convert = collect($data->result)->max(function ($item) {
            return count((array) $item->convert_params);
        });
        $this->materials = collect($data->result)
            ->groupBy(['object_id', 'material.category_id', 'material.manual_reference_id']);
        $this->objects = collect($data->result)->pluck('object.name_tag', 'object_id');
        $this->count = 0;

        $this->date = $customRequest->date;
        $this->request = $customRequest;
    }

    public function headings(): array
    {
        $filters = [];
        $rfilter = $this->request->filter ?? [];
        foreach ($rfilter as $filter) {
            $filters[] = $filter['parameter_text'].':'.$filter['value'];
        }

        return [
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', Carbon::now()->format('d.m.Y H:i')],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '196128, г. Санкт-Петербург, ул.Варшавская д. 9, к.1, литера А '],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', 'Тел.:', '+7(812) 922-76-96'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '+7 (812) 326-94-06'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', 'www.sk-gorod.com'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
            [
                '',
                '',
                'ОТЧЕТ ПО МАТЕРИАЛАМ НА ОБЪЕКТАХ',
            ],
            [
                '',
                '',
                'Отчет по состоянию материалов от «'.Carbon::now()->format('d.m.Y').'»',
            ],
            [
                '',
                '',
                'Отчет по состоянию материалов на «'.$this->date.'»',
            ],
            [
                '',
                '',
                'Фильтры: '.implode(', ', $filters),
            ],
            [
                '',
                '',
                '№',
                'Материал',
                'Длина',
                'Кол-во, шт',
                'Масса, т.',
                'Общая длина, м.п.',
                'Площадь, м2',
                'Объём, м3',
                'Примечание',
            ],
        ];
    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        foreach ($this->materials as $object_id => $category) {
            $subnumber = 1;
            $results->push(['', '', $number.'. '.$this->objects[$object_id]]);
            $this->count++;

            foreach ($category as $category_id => $etalons) {
                $results->push(['', '', ManualMaterialCategory::find($category_id)->name]);
                $this->count++;
                foreach ($etalons as $etalon_id => $materials) {
                    if ($etalon_id) {
                        $results->push(['', '', ManualReference::find($etalon_id)->name]);
                        $this->count++;
                    }
                    $materials = $materials->sortBy(function ($mat) {
                        return collect($mat->material->parameters)->where('name', 'Длина')->first()->value ?? '';
                    });
                    foreach ($materials as $material) {
                        $counts = collect();
                        $counts->push([
                            'unit' => $material->unit,
                            'count' => round($material->count, 3),
                        ]);

                        foreach ($material->convert_params as $param) {
                            $counts->push([
                                'unit' => $param->unit,
                                'count' => round($material->count * $param->value, 3),
                            ]);
                        }
                        if ($etalon_id) {
                            $mat_name = ManualReference::find($etalon_id)->name.' '.($material->used ? 'Б/У' : '');
                        } else {
                            $mat_name = $material->material_name;
                        }
                        $push = [];
                        $push[0] = '';
                        $push[1] = '';
                        $push[2] = $number.'.'.$subnumber++.' ';
                        $push[3] = $mat_name;
                        $push[4] = collect($material->material->parameters)->where('name', 'Длина')->first()->value ?? '';
                        $push[5] = $counts->where('unit', 'шт')->first()['count'] ?? '';
                        $push[6] = $counts->where('unit', 'т')->first()['count'] ?? '';
                        $push[7] = $counts->where('unit', 'м.п')->first()['count'] ?? '';
                        $push[8] = $counts->where('unit', 'м2')->first()['count'] ?? '';
                        $push[9] = $counts->where('unit', 'м3')->first()['count'] ?? '';

                        $push[10] = array_shift($material->comments)->comment ?? '';
                        $results->push($push);
                        $this->count++;

                        foreach ($material->comments as $comment) {
                            $results->push(['', '', '', '', '', '', '', '', '', '', $comment->comment]);
                            $this->count++;
                        }
                    }
                }
            }

            $number++;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->mergeCells('C1:D4');
                $event->sheet->getDelegate()->mergeCells('J1:K1');
                $event->sheet->getDelegate()->mergeCells('J2:K2');
                $event->sheet->getDelegate()->mergeCells('J3:K3');
                $event->sheet->getDelegate()->mergeCells('J4:K4');
                $event->sheet->getDelegate()->mergeCells('J5:K5');

                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(63);
                $event->sheet->getDelegate()->getStyle('J2:K2')->getAlignment()->setWrapText(true);

                $firstColumn = 'C7:L7';
                $event->sheet->getDelegate()->mergeCells($firstColumn);
                $secondColumn = 'C8:L8';
                $event->sheet->getDelegate()->mergeCells($secondColumn);
                $thirdColumn = 'C9:L9';
                $event->sheet->getDelegate()->mergeCells($thirdColumn);
                $thirdColumn = 'C10:L10';
                $event->sheet->getDelegate()->mergeCells($thirdColumn);
                $event->sheet->getStyle('C11:K'.($this->count + 11))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getStyle('E')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                $event->sheet->getStyle('G')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                $event->sheet->getStyle('I')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                $event->sheet->getStyle('C11:L11')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                $event->getSheet()->getStyle('E15:J500')->getNumberFormat()->setFormatCode('0.000');
                $event->sheet->horizontalAlign('C7:K500', \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->verticalAlign('C:P', \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $line = 12;
                //                dd($this->materials);
                foreach ($this->materials as $object_id => $category) {
                    $event->sheet->getDelegate()->mergeCells("C$line:K$line");
                    $event->sheet->horizontalAlign("C$line:K$line", \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $event->sheet->getStyle("C$line:K$line")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                        ]);
                    $event->sheet->getDelegate()->getRowDimension($line)->setRowHeight(28);
                    $event->sheet->getDelegate()->getStyle("C$line:K$line")->getAlignment()->setWrapText(true);
                    foreach ($category as $category_id => $etalons) {
                        $line += 1;

                        $event->sheet->getDelegate()->mergeCells("C$line:K$line");
                        $event->sheet->horizontalAlign("C$line:K$line", \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $event->sheet->getStyle("C$line:K$line")
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],
                            ]);
                        foreach ($etalons as $etalon_id => $materials) {
                            if ($etalon_id) {
                                $line += 1;

                                $event->sheet->getDelegate()->mergeCells("C$line:K$line");
                                $event->sheet->horizontalAlign("C$line:K$line", \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                                $event->sheet->getStyle("C$line:K$line")
                                    ->applyFromArray([
                                        'font' => [
                                            'bold' => true,
                                        ],
                                    ]);
                            }

                            foreach ($materials as $material) {
                                $line += count($material->comments) > 0 ? count($material->comments) + 1 : 1;
                            }
                        }

                    }
                    $line += 1;

                }
            },
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function title(): string
    {
        return 'Отчет по материалам';
    }

    public function export($fileName = 'Отчет по объектам.xlsx')
    {
        return $this->download($fileName);
    }

    /**
     * {@inheritDoc}
     */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('/img/logosvg.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('C1');

        return $drawing;
    }

    /**
     * {@inheritDoc}
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_GENERAL,
        ];
    }
}
