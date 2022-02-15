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
                'Заказчик'
            ]
        ];
    }

    public function collection()
    {
        $results = collect();
        $number = 1;

        foreach ($this->tasks as $task) {
            $results->push([
                $number,
                $task['project_name'],
                $task['project_address'],
                $task['contractor_name']
            ]);

            $number++;
        }


        $this->lastLineNumber = $number + 1;

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->setAutoFilter('A2:D2');
                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:D1');

                $event->sheet->getStyle('A1:D1')
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
                $event->sheet->getStyle('A2:D2')
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
                $event->sheet->getStyle('A'.self::startLineNumber.':D'.$this->lastLineNumber)
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
                    ->getStyle('A'.self::startLineNumber.':D'.$this->lastLineNumber)
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
            'C' => 106,
            'D' => 31
        ];
    }
}
