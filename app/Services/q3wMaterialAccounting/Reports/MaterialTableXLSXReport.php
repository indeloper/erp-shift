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

class MaterialTableXLSXReport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithDrawings, WithColumnFormatting, WithColumnWidths
{
    use Exportable;

    const startLineNumber = 12;
    /**
     * @var string
     */
    private $reportType;

    /**
     * @var array
     */
    private $date;
    private $filterText;
    private $borderStyleRulesArray;
    private $colorStyleRulesArray;
    /**
     * @var Collection
     */
    private $materials;

    /**
     * @var int
     */
    private $projectObjectId;
    private $lastLineNumber;

    public function __construct($projectObjectId, $materials, $filterText, $reportType)
    {
        $this->materials = $materials;
        $this->filterText = $filterText;
        $this->reportType = $reportType;
        $this->projectObjectId = $projectObjectId;
    }

    public function headings(): array
    {
        if (empty($this->filterText)) {
            $this->filterText = 'Не указаны';
        }

        return [
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',  ' ', Carbon::now()->format('d.m.Y H:i')],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',  ' ', '196128, г. Санкт-Петербург,'.PHP_EOL.'ул.Варшавская д. 9, к.1, литера А '],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',  'Тел.:', '+7 (812) 922-76-96'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',  ' ', '+7 (812) 326-94-06'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', 'www.sk-gorod.com'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',],
            [
                'ТАБЕЛЬ УЧЕТА МАТЕРИАЛОВ ОТ ' .  Carbon::now()->format('d.m.Y')
            ],
            [
                "Объект: " . ProjectObject::findOrFail($this->projectObjectId)->short_name
            ],
            [
                'Фильтры: ' . $this->filterText
            ],
            [

            ],
            [
                '№',
                'Дата',
                'Вид работ',
                'Наименование',
                'Кол-во (ед. изм.)',
                'Кол-во (шт.)',
                'Π ед.изм./шт',
                'Вес',
                'Приход',
                'Уход',
                'Комментарий',
                '№ ТТН',
                '№ ТН',
                'Индекс типа преобразования'
            ]
        ];
    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        $lineNumber = self::startLineNumber;
        $prevOperationId = 0;

        foreach ($this->materials as $material) {
            $results->push([
                $number,
                Carbon::parse($material['operation_date'])->format('d.m.Y'),
                $material['route_name'],
                $material['standard_name'],
                $material['quantity'],
                $material['amount'],
                '=E'.$lineNumber.'*F'.$lineNumber,
                '=ROUND(G'.$lineNumber.'*'.$material['standard_weight'].', 3)',
                $material['coming_from_project_object'],
                $material['outgoing_to_project_object'],
                $material['comment'],
                $material['item_transport_consignment_note_number'],
                $material['consignment_note_number'],
                $material['transform_operation_stage_id'],
            ]);

            if ($prevOperationId == 0) {
                $prevOperationId = $material['id'];
            }

            $borders = null;

            if ($prevOperationId == $material['id']) {
                $borders = [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '303030'),
                    ],
                ];
            } else {
                $borders = [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '303030'),
                    ],
                    'top' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => array('rgb' => '868686'),
                    ],
                ];
            }

            $isMaterialHasLeftProjectObject = false;

            switch ($material['operation_route_id']) {
                case 2:
                    if ($material['source_project_object_id'] == $this->projectObjectId) {
                        $isMaterialHasLeftProjectObject = true;
                    }
                    break;
                case 3:
                    if ($material['transform_operation_stage_id'] == 1) {
                        $isMaterialHasLeftProjectObject = true;
                    }
                    break;
                case 4:
                    $isMaterialHasLeftProjectObject = true;
                break;
            }

            if ($isMaterialHasLeftProjectObject) {
                $fontStyle = [
                    'color' => array('rgb' => '9C0006'),
                ];
                $fillStyle = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FCD5D4'),
                ];
            } else {
                $fontStyle = [
                    'color' => array('rgb' => '006100'),
                ];
                $fillStyle = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EBF1DE'),
                ];
            }

            $prevOperationId = $material['id'];

            $this->borderStyleRulesArray[$lineNumber] = ['borders' => $borders];
            $this->colorStyleRulesArray[$lineNumber] = ['font' => $fontStyle,'fill' => $fillStyle];

            $number++;
            $lineNumber ++;
        }

        $this->lastLineNumber = $lineNumber;
        return $results;
    }

    /**
     * @inheritDoc
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->setAutoFilter('A11:M11');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:G3'); //image logo
                $event->sheet->getDelegate()->mergeCells('L1:M1');
                $event->sheet->getDelegate()->mergeCells('L2:M2');
                $event->sheet->getDelegate()->mergeCells('L3:M3');
                $event->sheet->getDelegate()->mergeCells('L4:M4');
                $event->sheet->getDelegate()->mergeCells('L5:M5');
                $event->sheet->getDelegate()->mergeCells('A7:M7');
                $event->sheet->getDelegate()->mergeCells('A8:M8');
                $event->sheet->getDelegate()->mergeCells('A9:M9');
                $event->sheet->getDelegate()->mergeCells('A10:M10');

                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(63);

                $event->sheet->getDelegate()->getStyle('L2')->getAlignment()->setWrapText(true);

                $event->sheet->horizontalAlign('A7' , Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('K3' , Alignment::HORIZONTAL_RIGHT);
                $event->sheet->horizontalAlign('A8:E10' , Alignment::HORIZONTAL_LEFT);
                $event->sheet->horizontalAlign('L1:M6' , Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('C10')->getAlignment()->setWrapText(true);

                $event->sheet->getStyle('A7')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ]
                    ]);

                //Table headers
                $event->sheet->getStyle('A11:M11')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true

                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => array('rgb' => 'B8CCE4')
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('rgb' => '303030')
                            ],
                        ]
                    ]);

                if (!isset($this->borderStyleRulesArray)){
                    return;
                }

                foreach ($this->borderStyleRulesArray as $line => $style){
                    $event->sheet->getStyle('A'.$line.':M'.$line)->applyFromArray($style);
                }

                foreach ($this->colorStyleRulesArray as $line => $style){
                    $event->sheet->getStyle('C'.$line)->applyFromArray($style);
                }

                $event->sheet->getStyle('C'.($this->lastLineNumber - 1).':M'.($this->lastLineNumber - 1))
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN,
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
        return 'Табель учета материалов';
    }

    public function export($fileName = 'Табель учета материалов.xlsx')
    {
        return $this->download($fileName);
    }

    /**
     * @inheritDoc
     */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('/img/logosvg.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    /**
     * @inheritDoc
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_GENERAL,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 10,
            'C' => 19,
            'D' => 30,
            'F' => 16,
            'G' => 16,
            'E' => 16,
            'L' => 11,
            'M' => 11,
            'N' => 0
        ];
    }
}
