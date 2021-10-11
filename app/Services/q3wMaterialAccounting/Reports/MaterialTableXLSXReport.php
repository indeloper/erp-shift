<?php

namespace App\Services\q3wMaterialAccounting\Reports;

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
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MaterialTableXLSXReport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithDrawings, WithColumnFormatting, WithColumnWidths
{
    use Exportable;

    const startLineNumber = 12;

    /**
     * @var array
     */
    private $date;
    private $filterList;
    private $styleRulesArray;
    /**
     * @var Collection
     */
    private $materials;

    /**
     * @var int
     */
    private $lastLineNumber;

    public function __construct($materials, $filterList)
    {
        $this->materials = $materials;
        $this->filterList = $filterList;
    }

    public function headings(): array
    {
        $filterText = '';
        $filterTextArray = [];

        if (isset($this->filterList)) {
            foreach ($this->filterList as $filterItem) {
                $filterTextArray[] = $filterItem->text;
            }
            $filterText = implode($filterTextArray, '; ');
        } else {
            $filterText = 'Не указаны';
        }

        return [
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', Carbon::now()->format('d.m.Y H:i')],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '196128, г. Санкт-Петербург, ул.Варшавская д. 9, к.1, литера А '],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', 'Тел.:', '+7 (812) 922-76-96'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '+7 (812) 326-94-06'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', 'www.sk-gorod.com'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',],
            [
                '',
                '',
                'ОТЧЕТ ПО МАТЕРИАЛАМ НА ОБЪЕКТАХ'
            ],
            [
                '',
                '',
                "Отчет по состоянию материалов от «" . Carbon::now()->format('d.m.Y') . "»"
            ],
            [
                '',
                '',
                "Отчет по состоянию материалов на «" . $this->date . "»"
            ],
            [
                '',
                '',
                'Фильтры: '.$filterText
            ],
            [
                '',
                '',
                '№',
                '',
                '',
                'Материал',
                'Длина',
                'Площадь, м²',
                'Объём, м³',
                'Кол-во, шт',
                'Общая длина, м.п.',
                'Масса, т.',
                'Примечание',
            ]
        ];
    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        $lineNumber = self::startLineNumber;

        foreach ($this->materials as $objectKey => $objectValue) {
            $subNumber = 1;
            $results->push(['', '', $number . '. ' . $objectKey]);

            $this->styleRulesArray[] = ['styleName'=>'objectGroup', 'lineNumber'=>$lineNumber];
            $lineNumber ++;
            $objectStartIndex = $lineNumber;

            $objectsSummaryLineNumbers = [];
            foreach($objectValue as $materialTypeKey => $materialTypeValue) {
                $results->push(['', '', '', $number .'.'.$subNumber. '. ' . $materialTypeKey]);

                $this->styleRulesArray[] = ['styleName'=>'materialTypeGroup', 'lineNumber'=>$lineNumber];

                $materialTypeNumber = 1;
                $lineNumber++;


                $materialStandardSummaryLines = [];

                foreach ($materialTypeValue as $materialstandardKey => $materialStandardValue){
                    switch($materialStandardValue[0]->accounting_type) {
                        case 2:
                        $results->push(['', '', '', '', $number.'.'.$subNumber.'.'.$materialTypeNumber.'. '. $materialstandardKey]);

                        $this->styleRulesArray[] = ['styleName'=>'materialstandardGroup', 'lineNumber'=>$lineNumber];

                        $lineNumber++;
                        break;
                    }

                    $materialNumber = 1;

                    $materialSummaryLines = [];

                    switch($materialStandardValue[0]->accounting_type) {
                        case 2:
                            foreach ($materialStandardValue as $materialKey => $materialValue) {
                                $results->push(['',
                                    '',
                                    '',
                                    '',
                                    '',
                                    $number . '.' . $subNumber . '.' . $materialTypeNumber . '.' . $materialNumber . '. ' . $materialValue->standard_name,
                                    $materialValue->quantity,
                                    '',
                                    '',
                                    $materialValue->amount,
                                    $materialValue->amount * $materialValue->quantity,
                                    $materialValue->quantity * $materialValue->amount * $materialValue->weight,
                                    $materialValue->comment
                                ]);

                                $this->styleRulesArray[] = ['styleName'=>'materialData', 'lineNumber'=>$lineNumber, 'accountingType'=>$materialStandardValue[0]->accounting_type];

                                $materialSummaryLines[] = 'columnLetter'.$lineNumber;

                                $materialNumber++;
                                $lineNumber++;
                            }
                            break;
                        default:
                            $length = '';
                            $area = '';
                            $volume = '';
                            $amount = $materialStandardValue[0]->amount;

                            switch ($materialStandardValue[0]->measure_unit) {
                                case 1: //м.п
                                    $length = $materialStandardValue[0]->quantity;
                                    $commonLength = $materialStandardValue[0]->quantity * $materialStandardValue[0]->amount;
                                    break;
                                case 2:	//м²
                                    $area = $materialStandardValue[0]->quantity;
                                    break;
                                case 3:	//м³
                                    $volume = $materialStandardValue[0]->quantity;
                                    break;
                            }

                            switch ($materialStandardValue[0]->measure_unit) {
                                case 5:
                                    $mass = $materialStandardValue[0]->quantity;
                                    break;
                                default:
                                    $mass = $materialStandardValue[0]->quantity * $materialStandardValue[0]->amount * $materialStandardValue[0]->weight;
                            }

                            $results->push(['',
                                '',
                                '',
                                '',
                                '',
                                $number . '.' . $subNumber . '.' . $materialTypeNumber . '. ' . $materialStandardValue[0]->standard_name,
                                $length,
                                $area,
                                $volume,
                                $amount,
                                $commonLength,
                                $mass
                            ]);

                            $this->styleRulesArray[] = ['styleName'=>'materialData', 'lineNumber'=>$lineNumber, 'accountingType'=>$materialStandardValue[0]->accounting_type];

                            $materialStandardSummaryLines[] = 'columnLetter'.$lineNumber;

                            $lineNumber++;
                    }
                    $materialTypeNumber++;

                    switch($materialStandardValue[0]->accounting_type) {
                        case 2:
                            $results->push(['',
                                '',
                                '',
                                '',
                                '',
                                '',
                                '',
                                '='.str_replace('columnLetter', 'H', implode('+', $materialSummaryLines)),
                                '='.str_replace('columnLetter', 'I', implode('+', $materialSummaryLines)),
                                '='.str_replace('columnLetter', 'J', implode('+', $materialSummaryLines)),
                                '='.str_replace('columnLetter', 'K', implode('+', $materialSummaryLines)),
                                '='.str_replace('columnLetter', 'L', implode('+', $materialSummaryLines))]);

                            $this->styleRulesArray[] = ['styleName'=>'materialStandardGroupFooter', 'lineNumber'=>$lineNumber];
                            $materialStandardSummaryLines[] = 'columnLetter'.$lineNumber;
                            $lineNumber++;
                            break;
                    }
                }

                $results->push(['',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '='.str_replace('columnLetter', 'H', implode('+', $materialStandardSummaryLines)),
                    '='.str_replace('columnLetter', 'I', implode('+', $materialStandardSummaryLines)),
                    '='.str_replace('columnLetter', 'J', implode('+', $materialStandardSummaryLines)),
                    '='.str_replace('columnLetter', 'K', implode('+', $materialStandardSummaryLines)),
                    '='.str_replace('columnLetter', 'L', implode('+', $materialStandardSummaryLines))]);

                $this->styleRulesArray[] = ['styleName'=>'materialTypeGroupFooter', 'lineNumber'=>$lineNumber];
                $objectsSummaryLineNumbers[] = 'columnLetter'.$lineNumber;
                $lineNumber++;

                $subNumber++;
            }

            $results->push(['',
                '',
                '',
                '',
                '',
                '',
                '',
                '='.str_replace('columnLetter', 'H', implode('+', $objectsSummaryLineNumbers)),
                '='.str_replace('columnLetter', 'I', implode('+', $objectsSummaryLineNumbers)),
                '='.str_replace('columnLetter', 'J', implode('+', $objectsSummaryLineNumbers)),
                '='.str_replace('columnLetter', 'K', implode('+', $objectsSummaryLineNumbers)),
                '='.str_replace('columnLetter', 'L', implode('+', $objectsSummaryLineNumbers))]);

            $this->styleRulesArray[] = ['styleName'=>'objectGroupFooter', 'lineNumber'=>$lineNumber];
            $lineNumber ++;

            $number++;
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
                //$event->sheet->setAutoFilter('C11:K11');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('C1:G3'); //image logo
                $event->sheet->getDelegate()->mergeCells('C7:M7');
                $event->sheet->getDelegate()->mergeCells('C8:M8');
                $event->sheet->getDelegate()->mergeCells('C9:M9');
                $event->sheet->getDelegate()->mergeCells('C10:M10');
                $event->sheet->getDelegate()->mergeCells('C11:E11');

                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(63);

                $event->sheet->getDelegate()->getStyle('M2')->getAlignment()->setWrapText(true);

                $event->sheet->horizontalAlign('L3' , Alignment::HORIZONTAL_RIGHT);
                $event->sheet->horizontalAlign('C7' , Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('C8:E10' , Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('C10')->getAlignment()->setWrapText(true);

                $event->sheet->getStyle('C7')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ]
                    ]);

                //Table headers
                $event->sheet->getStyle('C11:M11')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('rgb' => '303030'),
                            ],
                        ]
                    ]);

                if (!isset($this->styleRulesArray)){
                    return;
                }

                foreach ($this->styleRulesArray as $styleRule) {
                    switch ($styleRule['styleName']) {
                        case 'objectGroup':
                            $event->sheet->getStyle('C'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ],
                                    'font' => [
                                        'bold' => true,
                                        'color' => array('rgb' => '303030'),
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('D'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'right' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);
                            $event->sheet->horizontalAlign('C'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'], Alignment::HORIZONTAL_LEFT);
                            break;
                        case 'objectGroupFooter':
                            $event->sheet->getStyle('D'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);
                                $event->sheet->getStyle('C'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                    ->applyFromArray([
                                    'font' => [
                                        'bold' => true,
                                        'color' => array('rgb' => '303030'),
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'right' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);
                            $event->sheet->horizontalAlign('C'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'], Alignment::HORIZONTAL_RIGHT);
                            break;
                        case 'materialTypeGroup':
                            $event->sheet->getStyle('D'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ],
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'D8E4BC'),
                                    ]
                                ]);

                            $event->sheet->getStyle('E'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'right' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ]
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('D'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->horizontalAlign('D'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_LEFT);
                            break;
                        case 'materialTypeGroupFooter':
                            $event->sheet->getStyle('E'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);
                                $event->sheet->getStyle('D'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                    ->applyFromArray([
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'D8E4BC'),
                                    ]
                                ]);

                            $event->sheet->getStyle('E'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'right' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ]
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('D'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->horizontalAlign('D'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_RIGHT);
                            break;
                        case 'materialstandardGroup':
                            $event->sheet->getStyle('E'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ],
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'DBE9F4'),
                                    ]
                                ]);

                            $event->sheet->getStyle('F'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'right' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ]
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('E'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('D'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'D8E4BC'),
                                    ]
                                ]);
                            $event->sheet->horizontalAlign('E'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_LEFT);
                            break;
                        case 'materialStandardGroupFooter':
                            $event->sheet->getStyle('F'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);
                            $event->sheet->getStyle('E'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'DBE9F4'),
                                    ]
                                ]);

                            $event->sheet->getStyle('F'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'right' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ]
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('E'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('D'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'D8E4BC'),
                                    ]
                                ]);
                            $event->sheet->horizontalAlign('E'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_RIGHT);
                            break;
                        case 'materialData':
                            $event->sheet->getStyle('F'.$styleRule
                                ['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('G'.$styleRule['lineNumber'].':M'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'allBorders' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('C'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ]
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'BDCBD6'),
                                    ]
                                ]);

                            $event->sheet->getStyle('E'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ]
                                ]);

                            $event->sheet->getStyle('D'.$styleRule['lineNumber'])
                                ->applyFromArray([
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => array('rgb' => '303030'),
                                        ],
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'D8E4BC'),
                                    ]
                                ]);

                            switch ($styleRule['accountingType']) {
                                case 2:
                                    $event->sheet->getStyle('F'.$styleRule['lineNumber'])
                                        ->applyFromArray([
                                            'borders' => [
                                                'left' => [
                                                    'borderStyle' => Border::BORDER_THIN,
                                                    'color' => array('rgb' => '303030'),
                                                ],
                                            ]
                                        ]);
                                    $event->sheet->getStyle('E'.$styleRule['lineNumber'])
                                        ->applyFromArray([
                                            'fill' => [
                                                'fillType' => Fill::FILL_SOLID,
                                                'color' => array('rgb' => 'DBE9F4'),
                                            ]
                                        ]);
                                    break;
                                default:
                                    $event->sheet->getStyle('E'.$styleRule['lineNumber'])
                                        ->applyFromArray([
                                            'borders' => [
                                                'bottom' => [
                                                    'borderStyle' => Border::BORDER_THIN,
                                                    'color' => array('rgb' => '303030'),
                                                ],
                                            ]
                                        ]);
                            }
                            $event->sheet->horizontalAlign('F'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_LEFT);
                            $event->sheet->horizontalAlign('M'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_LEFT);
                            $event->sheet->horizontalAlign('G'.$styleRule['lineNumber'].':'.'L'.$styleRule['lineNumber'] , Alignment::HORIZONTAL_RIGHT);

                            $event->sheet->getStyle('G'.$styleRule['lineNumber'])->getNumberFormat()
                                ->setFormatCode('0.00');
                            $event->sheet->getStyle('H'.$styleRule['lineNumber'])->getNumberFormat()
                                ->setFormatCode('0.00');
                            $event->sheet->getStyle('I'.$styleRule['lineNumber'])->getNumberFormat()
                                ->setFormatCode('0.00');
                            $event->sheet->getStyle('J'.$styleRule['lineNumber'])->getNumberFormat()
                                ->setFormatCode('0');
                            $event->sheet->getStyle('K'.$styleRule['lineNumber'])->getNumberFormat()
                                ->setFormatCode('0');
                            $event->sheet->getStyle('L'.$styleRule['lineNumber'])->getNumberFormat()
                                ->setFormatCode('0.000');
                    }

                    $event->sheet->getStyle('C'.($this->lastLineNumber - 1).':M'.($this->lastLineNumber - 1))
                        ->applyFromArray([
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => array('rgb' => '303030'),
                                ],
                            ]
                        ]);
                }
            }
        ];
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
            'A' => 2,
            'B' => 2,
            'C' => 2,
            'D' => 2,
            'E' => 2,
            'M' => 18
        ];
    }
}
