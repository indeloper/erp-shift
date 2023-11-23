<?php

namespace App\Services\ProjectObjectDocuments\Reports;


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

class ProjectObjectDocumentsXLSXReportGrouped implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithColumnWidths
{
    use Exportable;

    const startLineNumber = 5;
   
    /**
     * @var Collection
     */
    private $projectObjectDocuments;
    private $groupedBy;

    /**
     * @var int
     */

    private $lastLineNumber;
    private $statusStyles;
    private $groupLevel1NameLastElement;
    private $groupLevel2NameLastElement;
    private $lastObjectName;
    private $lastDocumentDate;
    private $Column1Merges;
    private $Column2Merges;
    private $Column3And4Merges;
    private $columnsDatesMerges;

    public function __construct($projectObjectDocuments, $groupedBy)
    {
        $this->projectObjectDocuments = $projectObjectDocuments;
        $this->groupedBy = $groupedBy;
        
        $this->statusStyles = [];
        $this->Column1Merges = [];
        $this->Column2Merges = [];
        $this->Column3And4Merges = [];
        $this->columnsDatesMerges = [];
    }

    public function headings(): array
    {
        if ($this->groupedBy === 'groupedByPM') {
            $groupedByDependedElements = ['Ответственные РП', 'Ответственные ПТО'];
        } 
        
        if ($this->groupedBy === 'groupedByPTO') {
            $groupedByDependedElements = ['Ответственные ПТО', 'Ответственные РП'];
        }
        
        $tableHeaders = array_merge(
            
            $groupedByDependedElements,

            [
                'Ответственные прорабы',
                'Объект',
                'Документ',
                'Дата создания',
                'Дней от создания',
                'Статус',
            ]

        );

        return [
            [
                'Площадка ⇆ Офис'
            ],
            [
                'Сформировано: '.now()->format('d.m.Y H:i')
            ]
            ,
            [

            ],
            $tableHeaders         
        ];

    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        $lineNumber = self::startLineNumber;
        
        foreach ($this->projectObjectDocuments as $groupLevel1Name=>$groupsLevel1) {

            foreach ($groupsLevel1 as $groupLevel2Name=>$groupsLevel2) {

                foreach ($groupsLevel2 as $projectObjectDocument) {

                    if ($this->groupedBy === 'groupedByPM') {
                        $groupedByDependedElements = [$projectObjectDocument['tongue_project_manager_full_names'] ?? null, $projectObjectDocument['tongue_pto_engineer_full_names'] ?? null];
                    } 
                    
                    if ($this->groupedBy === 'groupedByPTO') {
                        $groupedByDependedElements = [$projectObjectDocument['tongue_pto_engineer_full_names'] ?? null, $projectObjectDocument['tongue_project_manager_full_names'] ?? null];
                    }

                    $documentCreatedAtDate = Carbon::create($projectObjectDocument['created_at'])->format('d.m.Y') ?? null;
                    
                    $tableFields = array_merge(

                        $groupedByDependedElements,

                        [
                            $projectObjectDocument['tongue_foreman_full_names'] ?? null,
                            $projectObjectDocument['project_object']['short_name'],
                            $projectObjectDocument['document_name'] ?? null,
                            $documentCreatedAtDate,
                            $projectObjectDocument['days_from_doc_created'] ?? null,
                            $projectObjectDocument['status']['name'] ?? null, 
                        ]
                    );

                    $results->push($tableFields);

                    if ($groupLevel1Name != $this->groupLevel1NameLastElement) {
                        $this->Column1Merges[] = $lineNumber;
                    }

                    if ($groupLevel2Name != $this->groupLevel2NameLastElement) {
                        $this->Column2Merges[] = $lineNumber;
                    }

                    if ($projectObjectDocument['project_object']['short_name'] != $this->lastObjectName) {
                        $this->Column3And4Merges[] = $lineNumber;
                    }

                    if ($documentCreatedAtDate != $this->lastDocumentDate) {
                        $this->columnsDatesMerges[] = $lineNumber;
                    }

                    $this->groupLevel1NameLastElement = $groupLevel1Name;
                    $this->groupLevel2NameLastElement = $groupLevel2Name;
                    $this->lastObjectName = $projectObjectDocument['project_object']['short_name'];
                    $this->lastDocumentDate = $documentCreatedAtDate;

                    $this->statusStyles[$lineNumber] = str_replace('#', '', $projectObjectDocument['status']['project_object_documents_status_type']['style']);

                    $number++;
                    $lineNumber++;
                }
            }
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
                $event->sheet->setAutoFilter('A4:H4');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:'.'H1');
                $event->sheet->getDelegate()->mergeCells('A2:'.'H2');
                
                $event->sheet->horizontalAlign('A1', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('A2', Alignment::HORIZONTAL_RIGHT);
                
                $event->sheet->getStyle('A1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 18
                        ]
                    ]);

                
                //Table headers
                $event->sheet->getStyle('A4:'.'H4')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => array('rgb' => 'B8CCE4'),
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => array('rgb' => '303030')
                            ],
                        ],
                        'alignment' => [
                            'vertical' => 'top',
                            'horizontal' => 'center',
                            'wrapText' => true
                        ]
                    ]);

                    $event->sheet->getStyle('A' . self::startLineNumber . ':'. 'H' . $this->lastLineNumber)
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
                    
                        foreach($this->statusStyles as $key=>$value) {
                            $event->sheet->getStyle('H' . $key )
                                ->applyFromArray([
                                    'font' => [
                                        'color' => array('rgb' => self::CELLS_COLOR[$value]['color']),
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => array('rgb' => self::CELLS_COLOR[$value]['background']),
                                    ],
                                ]);
                        }  

                        $event->sheet->getStyle('A' . self::startLineNumber . ':'. 'C' . $this->lastLineNumber)
                        ->applyFromArray([
                            'alignment' => [
                                'vertical' => 'center',
                                'horizontal' => 'center',
                                'textRotation' => 90,
                                'wrapText' => true
                            ],
                        ]);

                        $event->sheet->getStyle('D' . self::startLineNumber . ':'. 'H' . $this->lastLineNumber)
                        ->applyFromArray([
                            'alignment' => [
                                'vertical' => 'center',
                                'wrapText' => true
                            ],
                        ]);
                        
                        
                        $previousMergeBreakpoint = self::startLineNumber;
                        $i = 0;
                        foreach ($this->Column1Merges as $lineNumber) {
                            if ($i>0) {
                                $event->sheet->getDelegate()->mergeCells('A'.$previousMergeBreakpoint.':'.'A'.($lineNumber-1));
                            }
                                
                            $i++;
                            $previousMergeBreakpoint = $lineNumber;
                        }

                        $previousMergeBreakpoint = self::startLineNumber;
                        $i = 0;
                        foreach ($this->Column2Merges as $lineNumber) {
                            if ($i>0) 
                                $event->sheet->getDelegate()->mergeCells('B'.$previousMergeBreakpoint.':'.'B'.($lineNumber-1));
                            $i++;
                            $previousMergeBreakpoint = $lineNumber;
                        }

                        $previousMergeBreakpoint = self::startLineNumber;
                        $i = 0;
                        foreach ($this->Column3And4Merges as $lineNumber) {
                            if ($i>0) {
                                $event->sheet->getDelegate()->mergeCells('C'.$previousMergeBreakpoint.':'.'C'.($lineNumber-1));
                                $event->sheet->getDelegate()->mergeCells('D'.$previousMergeBreakpoint.':'.'D'.($lineNumber-1));
                            }   
                            $i++;
                            $previousMergeBreakpoint = $lineNumber;
                        }

                        $previousMergeBreakpoint = self::startLineNumber;
                        $i = 0;
                        foreach ($this->columnsDatesMerges as $lineNumber) {
                            if ($i>0) {
                                $event->sheet->getDelegate()->mergeCells('F'.$previousMergeBreakpoint.':'.'F'.($lineNumber-1));
                                $event->sheet->getDelegate()->mergeCells('G'.$previousMergeBreakpoint.':'.'G'.($lineNumber-1));
                            }   
                            $i++;
                            $previousMergeBreakpoint = $lineNumber;
                        }

            }
        ];
    }

    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return 'Площадка ⇆ Офис';
    }

    public function export()
    {
        $fileName = 'Площадка ⇆ Офис '.now().'.xlsx';
        return $this->download($fileName);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 10,
            'C' => 10,
            'D' => 50,
            'E' => 50,
            'F' => 30,
            'G' => 30,
            'H' => 30,
            'I' => 30,
            'J' => 30,
        ];
    }

    const CELLS_COLOR = [
        'dd5e5e' => [
            'color' => '9c0006',
            'background' => 'ffc7ce'
        ],
        'ffcd72' => [
            'color' => '9c5700',
            'background' => 'ffeb9c'
        ],
        '1f931f' => [
            'color' => '006100',
            'background' => 'c6efce'
        ],
        'c5c7c5' => [
            'color' => '3f3f3f',
            'background' => 'f2f2f2'
        ],
    ];
}
