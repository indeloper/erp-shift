<?php


namespace App\Services\HumanResources\Reports;


use App\Models\HumanResources\TimecardRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ReportGroupSheet implements FromArray, ShouldAutoSize, WithTitle, WithEvents, WithHeadings
{
    use Exportable;

    private $users;
    /**
     * @var int
     */
    private $rows_count;
    /**
     * @var array
     */
    private $collection;
    /**
     * @var array
     */
    private $user_rows_count;

    private $orange = 'FFE699';
    private $green = 'c6e0b4';
    private $blue = 'd9e1f2';
    private $red = 'f8cbad';

    public function __construct($users)
    {
        $this->users = $users;

        $collection = [];
        $user_rows_count = [];

        foreach ($this->users as $key => $timecard) {
            foreach ($this->collectDataForUser($timecard) as $user_row) {
                $collection[] = $user_row;
            }
            $user_rows_count[$key] = [
                'tariff_count' => $timecard->user->jobCategory->tariffs()->count(),
                'ktu_count' => 1,
                'bonuses_count' => $timecard->additions->whereIn('type', [1, 3])->count(),
                'sum_count' => 1,
                'fines_count' => $timecard->additions->where('type', 2)->count(),
                'total_count' => 1,
            ];
        }
        $this->user_rows_count = $user_rows_count;
        $this->collection = $collection;
    }


    public function array(): array
    {
        return $this->collection;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $header_count = 1;
                $prev_count = 1 + $header_count;
                $curr_count = 1;

                foreach ($this->user_rows_count as $one_user) {
                    foreach ($one_user as $key => $rows_counts) {
                        if ($rows_counts > 1) {
                            $this->innerBorderForContent($event, $curr_count, $rows_counts);
                        }
                        switch ($key) {
                            case ('tariff_count'):
                                $this->colorSection($event, $curr_count, $rows_counts, $this->blue);
                                break;
                            case ('bonuses_count'):
                                $this->colorSection($event, $curr_count, $rows_counts, $this->green);
                                break;
                            case('fines_count'):
                                $this->colorSection($event, $curr_count, $rows_counts, $this->red);
                                break;
                            case ('total_count'):
                                $this->colorSection($event, $curr_count, $rows_counts, $this->orange);
                                $this->setBoldSection($event, $curr_count, $rows_counts);
                                break;
                        }

                        $curr_count += $rows_counts;
                        $this->bottomBorderForSection($event, $curr_count);
                    }

                    $rows_range = "A{$prev_count}:A{$curr_count}";
                    $this->borderForUserName($event, $rows_range);
                    $this->mergeCellsForName($event, $rows_range);

                    $this->borderForContent($event, $curr_count);

                    $prev_count = $curr_count + 1;
                }
            }
        ];
    }

    public function title(): string
    {
        return $this->users->first()->user->reportGroup->name;
    }

    public function headings(): array
    {
        return [
            'ФИО', 'Наименование', 'Кол-во/Проект', 'Ставка за ед. (руб.)', 'Сумма (руб.)'
        ];
    }

    public function addEmptyCells($array, $cells_count = 1)
    {
        while ($cells_count > 0) {
            $array[] = '';
            $cells_count--;
        }
        return $array;
    }

    /**
     * @param $user
     * @param array $collection
     * @return array
     */
    public function collectDataForUser($timecard): array
    {
        $collection = [];
        $sum = 0;

        foreach ($timecard->user->jobCategory->tariffs as $tariff) {
            $tariff_row = [''];

            $amount = $timecard->records->where('type', TimecardRecord::TYPES_ENG['working hours'])->where('tariff_id', $tariff->tariff_id)->sum('amount');
            $tariff_row[] = $tariff->name;
            $tariff_row[] = "$amount";
            $tariff_row[] = $tariff->rate;
            $tariff_row[] = "". ($tariff->rate * $amount) ."";
            $sum += $tariff->rate * $amount;
            array_push($collection, $tariff_row);
        }

        $ktu_row = [''];
        $ktu_row[] = 'КТУ (%)';
        $ktu_row = $this->addEmptyCells($ktu_row, 2);
        $ktu_row[] = $timecard->ktu ?: 100;
        array_push($collection, $ktu_row);

        foreach ($timecard->additions->whereIn('type', [1, 3]) as $addition) {
            $additions_row = [];
            $additions_row = $this->addEmptyCells($additions_row, 1);
            $additions_row[] = $addition->name;
            $additions_row[] = ($addition->type == 3 ? $addition->project->name : '');
            $additions_row = $this->addEmptyCells($additions_row, 1);
            $additions_row[] = $addition->amount;
            $sum += $addition->amount;
            array_push($collection, $additions_row);
        }

        $sum_row = [];
        $sum_row = $this->addEmptyCells($sum_row, 1);
        $sum_row[] = 'Сумма';
        $sum_row = $this->addEmptyCells($sum_row, 2);
        $sum = $sum * ($timecard->ktu == 0 ? 100 : $timecard->ktu) / 100;
        $sum_row[] = $sum;
        array_push($collection, $sum_row);

        $fines = 0;
        foreach ($timecard->additions->where('type', 2) as $fine) {
            $fines_row = [];
            $fines_row = $this->addEmptyCells($fines_row, 1);
            $fines_row[] = $fine->name;
            $fines_row[] = $fine->project->name ?? 'удалённый проект';
            $fines_row = $this->addEmptyCells($fines_row, 1);
            $fines_row[] = $fine->amount;
            $fines += $fine->amount;
            array_push($collection, $fines_row);
        }

        $total_row = [];
        $total_row = $this->addEmptyCells($total_row, 1);
        $total_row[] = 'Итого';
        $total_row = $this->addEmptyCells($total_row, 2);
        $total_row[] = $sum - $fines;
        array_push($collection, $total_row);
        $this->rows_count = count($collection);
        $collection[0][0] = $timecard->user->long_full_name;
        return $collection;
    }

    /**
     * @param AfterSheet $event
     * @param int $curr_count
     */
    public function bottomBorderForSection(AfterSheet $event, int $curr_count): void
    {
        $event->sheet->getStyle('B' . $curr_count . ':E' . $curr_count)
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(true);
    }

    /**
     * @param AfterSheet $event
     * @param string $rows_range
     */
    public function borderForUserName(AfterSheet $event, string $rows_range): void
    {
        $event->sheet->getStyle($rows_range)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(true);
    }

    /**
     * @param AfterSheet $event
     * @param string $rows_range
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function mergeCellsForName(AfterSheet $event, string $rows_range): void
    {
        $event->sheet->getDelegate()->mergeCells($rows_range);
        $event->sheet->horizontalAlign($rows_range, \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $event->sheet->verticalAlign($rows_range, \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    }

    /**
     * @param AfterSheet $event
     * @param int $curr_count
     */
    public function borderForContent(AfterSheet $event, int $curr_count): void
    {
        $event->sheet->getStyle('B' . 2 . ':E' . $curr_count)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(true);
    }

    /**
     * @param AfterSheet $event
     * @param int $curr_count
     * @param $rows_counts
     */
    public function innerBorderForContent(AfterSheet $event, int $curr_count, $rows_counts): void
    {
        $event->sheet->getStyle('C' . ($curr_count + 1) . ':E' . ($curr_count + $rows_counts))
            ->getBorders()
            ->getHorizontal()
            ->setBorderStyle('thin');
    }

    /**
     * @param AfterSheet $event
     * @param int $curr_count
     * @param $rows_counts
     * @param $color
     */
    public function colorSection(AfterSheet $event, int $curr_count, $rows_counts, $color): void
    {
        $event->sheet->getStyle('B' . ($curr_count + 1) . ':E' . ($curr_count + $rows_counts))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($color);
    }

    /**
     * @param AfterSheet $event
     * @param int $curr_count
     * @param $rows_counts
     */
    public function setBoldSection(AfterSheet $event, int $curr_count, $rows_counts): void
    {
        $event->sheet->getStyle('B' . ($curr_count + 1) . ':E' . ($curr_count + $rows_counts))->getFont()->setBold(true);
    }
}
