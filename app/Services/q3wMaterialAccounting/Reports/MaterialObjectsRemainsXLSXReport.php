<?php

namespace App\Services\q3wMaterialAccounting\Reports;

use App\Models\ProjectObject;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MaterialObjectsRemainsXLSXReport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithColumnWidths
{
    use Exportable;

    const startLineNumber = 7;
    /**
     * @var string
     */
    private $date;

    /**
     * @var array
     */
    private $filterText;
    private $borderStyleRulesArray;
    private $colorStyleRulesArray;
    /**
     * @var Collection
     */
    private $materialRemains;

    /**
     * @var int
     */
    private $projectObjectId;
    private $lastLineNumber;

    public function __construct($projectObjectId, $materialRemains, $filterText, $date)
    {
        $this->materialRemains = $materialRemains;
        $this->filterText = $filterText;
        $this->date = $date;
        $this->projectObjectId = $projectObjectId;
    }

    public function headings(): array
    {
        if (empty($this->filterText)) {
            $this->filterText = 'Не указаны';
        }

        return [
            [
                'Остатки на объектах'
            ],
            [
                'Фильтры: ' . $this->filterText
            ],
            [

            ],
            [
                'Объект',
                'Наименование',
                'Количество',
                'Количество (шт.)',
                'Вес'
            ]
            
        ];

    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        $lineNumber = self::startLineNumber;

        foreach ($this->materialRemains as $material) {

            $results->push([
                $material['object_name'],
                $material['standard_name'],
                $material['amount'] . " " . $material['unit_measure_value'],
                $material['quantity'],
                $material['summary_weight'],

            ]);

            $number++;
            $lineNumber++;
        }

        $this->lastLineNumber = $lineNumber - 1;
        return $results;
    }

    /**
     * @inheritDoc
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->setAutoFilter('A4:E4');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:E1');
                
                $event->sheet->horizontalAlign('A1', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('A2', Alignment::HORIZONTAL_LEFT);

                
                $event->sheet->getStyle('A1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ]
                    ]);

                
                //Table headers
                $event->sheet->getStyle('A4:E4')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true

                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => array('rgb' => '303030')
                            ],
                        ]
                    ]);

                // $event->sheet->getStyle('A' . self::startLineNumber . ':A' . $this->lastLineNumber)
                //     ->applyFromArray([
                //         'font' => [
                //             'bold' => true

                //         ],
                //         'alignment' => [
                //             'horizontal' => Alignment::HORIZONTAL_RIGHT,
                //         ],
                //         'borders' => [
                //             'allBorders' => [
                //                 'borderStyle' => Border::BORDER_THIN,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //             'outline' => [
                //                 'borderStyle' => Border::BORDER_MEDIUM,
                //                 'color' => array('rgb' => '303030')
                //             ]
                //         ]
                //     ]);

                // $event->sheet->getStyle('B' . self::startLineNumber . ':D' . $this->lastLineNumber)
                //     ->applyFromArray([
                //         'font' => [
                //             'color' => array('rgb' => '335633'),
                //         ],
                //         'fill' => [
                //             'fillType' => Fill::FILL_SOLID,
                //             'color' => array('rgb' => 'dbf7b1'),
                //         ],
                //         'borders' => [
                //             'allBorders' => [
                //                 'borderStyle' => Border::BORDER_THIN,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //             'outline' => [
                //                 'borderStyle' => Border::BORDER_MEDIUM,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //         ]
                //     ]);

                // $event->sheet->getStyle('E' . self::startLineNumber . ':G' . $this->lastLineNumber)
                //     ->applyFromArray([
                //         'font' => [
                //             'color' => array('rgb' => '762828'),
                //         ],
                //         'fill' => [
                //             'fillType' => Fill::FILL_SOLID,
                //             'color' => array('rgb' => 'fbb1b1'),
                //         ],
                //         'borders' => [
                //             'allBorders' => [
                //                 'borderStyle' => Border::BORDER_THIN,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //             'outline' => [
                //                 'borderStyle' => Border::BORDER_MEDIUM,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //         ]
                //     ]);

                // $event->sheet->getStyle('H' . self::startLineNumber . ':J' . $this->lastLineNumber)
                //     ->applyFromArray([
                //         'font' => [
                //             'color' => array('rgb' => '20205a'),
                //         ],
                //         'fill' => [
                //             'fillType' => Fill::FILL_SOLID,
                //             'color' => array('rgb' => 'bdbdf7'),
                //         ],
                //         'borders' => [
                //             'allBorders' => [
                //                 'borderStyle' => Border::BORDER_THIN,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //             'outline' => [
                //                 'borderStyle' => Border::BORDER_MEDIUM,
                //                 'color' => array('rgb' => '303030')
                //             ],
                //         ]
                //     ]);
            }
        ];
    }

    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return 'Остатки на объектах';
    }

    public function export($fileName = 'Остатки на объектах.xlsx')
    {
        return $this->download($fileName);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 50,
            'C' => 20,
            'D' => 20,
            'E' => 20
        ];
    }
}
