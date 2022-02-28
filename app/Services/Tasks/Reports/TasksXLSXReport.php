<?php

namespace App\Services\Tasks\Reports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TasksXLSXReport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithColumnFormatting, WithColumnWidths
{
    use Exportable;

    const startLineNumber = 3;

    /**
     * @var array
     */
    private $mergeTaskArray = [];
    private $mergeCommercialOfferArray = [];

    private $date;
    /**
     * @var Collection
     */
    private $tasks;

    /**
     * @var int
     */
    private $lastLineNumber;

    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    public function headings(): array
    {
        return [
            [
                'Отчет по задачам от '.Carbon::now()->format('d.m.Y')
            ],
            [
                '№ п/п',
                'Работа',
                'Адрес',
                'Заказчик',
                'Коммерческое предложение',
                'Комментарий',
                'Номенклатура',
                'Тоннаж'
            ]
        ];
    }

    public function collection()
    {
        $results = collect();
        $taskNumber = 1;

        $taskMergeStartIndex = 3;
        $taskMergeEndIndex = 3;

        $commercialOfferMergeStartIndex = 3;
        $commercialOfferMergeEndIndex = 3;

        foreach ($this->tasks as $task) {
            foreach($task as $commercialOffer) {
                foreach($commercialOffer as $material) {
                    $results->push([
                        $taskNumber,
                        $material['project_name'],
                        $material['project_address'],
                        $material['contractor_name'],
                        $material['commercial_offers_title'],
                        $material['final_note'],
                        $material['material_name'],
                        $material['material_count']
                    ]);

                    $taskMergeEndIndex++;
                    $commercialOfferMergeEndIndex++;
                }
                $this->mergeCommercialOfferArray[] = [$commercialOfferMergeStartIndex, $commercialOfferMergeEndIndex - 1];
                $commercialOfferMergeStartIndex = $commercialOfferMergeEndIndex;
            }
            $taskNumber++;
            $this->mergeTaskArray[] = [$taskMergeStartIndex, $taskMergeEndIndex - 1];
            $taskMergeStartIndex = $taskMergeEndIndex;
        }

        $this->lastLineNumber = $taskMergeEndIndex - 1;

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                //$event->sheet->setAutoFilter('A2:D2');
                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:H1');

                // Cell Merging
                foreach ($this->mergeTaskArray as $mergeArray){
                    $event->sheet->getDelegate()->mergeCells('A'.$mergeArray[0].':A'.$mergeArray[1]);
                    $event->sheet->getDelegate()->mergeCells('B'.$mergeArray[0].':B'.$mergeArray[1]);
                    $event->sheet->getDelegate()->mergeCells('C'.$mergeArray[0].':C'.$mergeArray[1]);
                    $event->sheet->getDelegate()->mergeCells('D'.$mergeArray[0].':D'.$mergeArray[1]);
                }

                foreach ($this->mergeCommercialOfferArray as $mergeArray){
                    $event->sheet->getDelegate()->mergeCells('E'.$mergeArray[0].':E'.$mergeArray[1]);
                    $event->sheet->getDelegate()->mergeCells('F'.$mergeArray[0].':F'.$mergeArray[1]);
                }

                $event->sheet->getStyle('A1:H1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('rgb' => '303030'),
                            ],
                        ]
                    ]);

                //Table headers
                $event->sheet->getStyle('A2:H2')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('rgb' => '303030'),
                            ],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FDE9D9'),
                        ]
                    ]);

                //Table data
                $event->sheet->getStyle('A' . self::startLineNumber . ':H' . $this->lastLineNumber)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('rgb' => '303030'),
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                    ]);

                $event->sheet->getDelegate()
                    ->getStyle('A' . self::startLineNumber . ':H' . $this->lastLineNumber)
                    ->getAlignment()
                    ->setWrapText(true);
            }
        ];
    }

    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return 'Отчет по задачам';
    }

    public function export($fileName = 'Отчет по задачам.xlsx')
    {
        return $this->download($fileName);
    }

    /**
     * @inheritDoc
     */
    public function columnFormats(): array
    {
        return [

        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 34,
            'C' => 85,
            'D' => 31,
            'F' => 40,
            'E' => 31
        ];
    }
}
