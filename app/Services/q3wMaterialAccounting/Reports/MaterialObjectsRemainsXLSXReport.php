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

    const startLineNumber = 5;
    /**
     * @var string
     */

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

    private $lastLineNumber;

    public function __construct($materialRemains, $filterText)
    {
        $this->materialRemains = $materialRemains;
        $this->filterText = $filterText;
        $this->lastColumn = array_key_exists('comment', $materialRemains[0]) ? 'G' : 'F';
    }

    public function headings(): array
    {
        if (empty($this->filterText)) {
            $this->filterText = 'Не указаны';
        }

        $tableHeaders = [
            'Объект',
            'Наименование',
            'Количество',
            'Ед.Изм.',
            'Количество (шт.)',
            'Вес'
        ];

        if($this->lastColumn == 'G')
        $tableHeaders[] = 'Комментарий';

        return [
            [
                'Остатки на объектах'
            ],
                $this->getRow2Arr()
            ,
            [

            ],
            $tableHeaders         
        ];

    }

    function getRow2Arr()
    {
        $content = ['Фильтры: ' . $this->filterText];
        
        $this->lastColumn == 'G' ? $emtyCells = 5 : $emtyCells = 4;
        for($i=1; $i<=$emtyCells; $i++)
        $content[] = '';

        $content[] = now()->format('d.m.Y');
        return $content;
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
                $material['quantity'],
                $material['unit_measure_value'],
                $material['amount'], 
                $material['summary_weight'],
                $material['comment'] ?? NULL
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
                $event->sheet->setAutoFilter('A4:F4');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:'.$this->lastColumn.'1');
                
                $event->sheet->horizontalAlign('A1', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('A2', Alignment::HORIZONTAL_LEFT);
                $event->sheet->horizontalAlign($this->lastColumn.'2', Alignment::HORIZONTAL_RIGHT);

                
                $event->sheet->getStyle('A1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 18
                        ]
                    ]);

                
                //Table headers
                $event->sheet->getStyle('A4:'.$this->lastColumn.'4')
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

                    $event->sheet->getStyle('A' . self::startLineNumber . ':'.$this->lastColumn . $this->lastLineNumber)
                        ->applyFromArray([
                           
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => array('rgb' => '303030')
                                ],
                                'outline' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color' => array('rgb' => '303030')
                                ],
                            ]
                        ]);
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
            'E' => 20,
            'F' => 20,
            'G' => 40
        ];
    }
}
