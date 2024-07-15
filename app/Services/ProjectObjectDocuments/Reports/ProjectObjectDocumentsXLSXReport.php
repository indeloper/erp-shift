<?php

namespace App\Services\ProjectObjectDocuments\Reports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectObjectDocumentsXLSXReport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithTitle
{
    use Exportable;

    const startLineNumber = 5;

    /**
     * @var Collection
     */
    private $projectObjectDocuments;

    /**
     * @var int
     */
    private $lastLineNumber;

    private $statusStyles;

    public function __construct($projectObjectDocuments, $statusStyles = [])
    {
        $this->projectObjectDocuments = $projectObjectDocuments;
        $this->statusStyles = $statusStyles;
    }

    public function headings(): array
    {
        $tableHeaders = [
            'Объект',
            'Документ',
            'Дата документа',
            'Дата создания',
            'Дней от создания',
            'Тип',
            'Статус',
            'Дата изменения статуса',
            'Ответственные РП',
            'Ответственные ПТО',
            'Ответственные прорабы',
        ];

        return [
            [
                'Площадка ⇆ Офис',
            ],
            [
                'Сформировано: '.now()->format('d.m.Y H:i'),
            ],
            [

            ],
            $tableHeaders,
        ];

    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        $lineNumber = self::startLineNumber;

        foreach ($this->projectObjectDocuments as $projectObjectDocument) {

            $results->push([
                $projectObjectDocument['project_object']['short_name'],
                $projectObjectDocument['document_name'] ?? null,
                $projectObjectDocument['document_date'] ?? null,
                Carbon::create($projectObjectDocument['created_at'])->format('d.m.Y') ?? null,
                $projectObjectDocument['days_from_doc_created'] ?? null,
                $projectObjectDocument['type']['name'] ?? null,
                $projectObjectDocument['status']['name'] ?? null,
                $projectObjectDocument['status_updated_at'] ?? null,
                $projectObjectDocument['tongue_project_manager_full_names'] ?? null,
                $projectObjectDocument['tongue_pto_engineer_full_names'] ?? null,
                $projectObjectDocument['tongue_foreman_full_names'] ?? null,
            ]);

            $this->statusStyles[$lineNumber] = str_replace('#', '', $projectObjectDocument['status']['project_object_documents_status_type']['style']);

            $number++;
            $lineNumber++;
        }

        $this->lastLineNumber = $lineNumber - 1;

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->setAutoFilter('A4:K4');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:'.'K1');
                $event->sheet->getDelegate()->mergeCells('A2:'.'K2');

                $event->sheet->horizontalAlign('A1', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('A2', Alignment::HORIZONTAL_RIGHT);

                $event->sheet->getStyle('A1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 18,
                        ],
                    ]);

                //Table headers
                $event->sheet->getStyle('A4:'.'K4')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'B8CCE4'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '303030'],
                            ],
                        ],
                    ]);

                $event->sheet->getStyle('A'.self::startLineNumber.':'.'K'.$this->lastLineNumber)
                    ->applyFromArray([

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '303030'],
                            ],
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '303030'],
                            ],
                        ],
                    ]);

                foreach ($this->statusStyles as $key => $value) {
                    $event->sheet->getStyle('F'.$key)
                        ->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => self::CELLS_COLOR[$value]['color']],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => self::CELLS_COLOR[$value]['background']],
                            ],
                        ]);
                }
            },
        ];
    }

    /**
     * {@inheritDoc}
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
            'A' => 50,
            'B' => 30,
            'C' => 30,
            'D' => 30,
            'E' => 30,
            'F' => 30,
            'G' => 30,
            'H' => 30,
            'I' => 30,
            'J' => 30,
            'K' => 30,
        ];
    }

    const CELLS_COLOR = [
        'dd5e5e' => [
            'color' => '9c0006',
            'background' => 'ffc7ce',
        ],
        'ffcd72' => [
            'color' => '9c5700',
            'background' => 'ffeb9c',
        ],
        '1f931f' => [
            'color' => '006100',
            'background' => 'c6efce',
        ],
        'c5c7c5' => [
            'color' => '3f3f3f',
            'background' => 'f2f2f2',
        ],
    ];
}
