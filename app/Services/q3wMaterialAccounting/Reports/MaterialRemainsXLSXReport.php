<?php

namespace App\Services\q3wMaterialAccounting\Reports;

use App\Models\ProjectObject;
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

class MaterialRemainsXLSXReport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithTitle
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
                'Остатки материалов на '.Carbon::parse($this->date)->format('d.m.Y'),
            ],
            [
                'Фильтры: '.$this->filterText,
            ],
            [

            ],
            [
                ProjectObject::findOrFail($this->projectObjectId)->short_name,
            ],
            [
                'Наименование',
                'Завоз',
                '',
                '',
                'Вывоз',
                '',
                '',
                'Остаток',
            ],
            [
                '',
                'шт.',
                'п.м./м²',
                'тн.',
                'шт.',
                'п.м./м²',
                'тн.',
                'шт.',
                'п.м./м²',
                'тн.',
            ],
        ];
    }

    public function collection()
    {
        $results = collect();
        $number = 1;
        $lineNumber = self::startLineNumber;

        foreach ($this->materialRemains as $material) {
            switch ($material['accounting_type']) {
                case 1:
                    if (round($material['coming_to_material_weight'] - $material['outgoing_material_material_weight'], 3) != 0) {
                        $amount = 1;
                    } else {
                        $amount = 0;
                    }
                    break;
                default:
                    $amount = '=B'.$lineNumber.'-E'.$lineNumber;
            }

            $results->push([
                $material['standard_name'],
                (string) $material['coming_to_material_amount'],
                (string) $material['coming_to_material_quantity'],
                (string) $material['coming_to_material_weight'],
                (string) $material['outgoing_material_amount'],
                (string) $material['outgoing_material_quantity'],
                (string) $material['outgoing_material_material_weight'],
                (string) $amount,
                '=C'.$lineNumber.'-F'.$lineNumber,
                '=D'.$lineNumber.'-G'.$lineNumber,
            ]);

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
                $event->sheet->setAutoFilter('A6:J6');

                //Main header styles
                $event->sheet->getDelegate()->mergeCells('A1:J1');
                $event->sheet->getDelegate()->mergeCells('A2:J2');
                $event->sheet->getDelegate()->mergeCells('A4:J4');
                $event->sheet->getDelegate()->mergeCells('A5:A6');
                $event->sheet->getDelegate()->mergeCells('B5:D5');
                $event->sheet->getDelegate()->mergeCells('E5:G5');
                $event->sheet->getDelegate()->mergeCells('H5:J5');

                $event->sheet->horizontalAlign('A1', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('A2', Alignment::HORIZONTAL_LEFT);

                $event->sheet->horizontalAlign('A4', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('A5', Alignment::HORIZONTAL_CENTER);
                $event->sheet->verticalAlign('A5', Alignment::VERTICAL_CENTER);
                $event->sheet->horizontalAlign('B5', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('E5', Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('H5', Alignment::HORIZONTAL_CENTER);

                $event->sheet->getStyle('A1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                $event->sheet->getStyle('A4')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                //Table headers
                $event->sheet->getStyle('A5:J6')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,

                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '303030'],
                            ],
                        ],
                    ]);

                $event->sheet->getStyle('A'.self::startLineNumber.':A'.$this->lastLineNumber)
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,

                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        ],
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

                $event->sheet->getStyle('B'.self::startLineNumber.':D'.$this->lastLineNumber)
                    ->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => '335633'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'dbf7b1'],
                        ],
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

                $event->sheet->getStyle('E'.self::startLineNumber.':G'.$this->lastLineNumber)
                    ->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => '762828'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'fbb1b1'],
                        ],
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

                $event->sheet->getStyle('H'.self::startLineNumber.':J'.$this->lastLineNumber)
                    ->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => '20205a'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'bdbdf7'],
                        ],
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
            },
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function title(): string
    {
        return 'Остатки материалов';
    }

    public function export($fileName = 'Остатки материалов.xlsx')
    {
        return $this->download($fileName);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 10,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 10,
        ];
    }
}
