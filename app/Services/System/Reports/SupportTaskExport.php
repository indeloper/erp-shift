<?php

namespace App\Services\System\Reports;

use App\Models\SupportMail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SupportTaskExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    use Exportable;

    private $tasks;

    public function __construct()
    {
       $this->tasks = SupportMail::with('sender')->whereIn('status', ['new', 'in_work', 'matching', 'accept', 'decline', 'check', 'development'])->get();
    }

    public function title(): string
    {
        return 'Задачи';
    }

    public function headings(): array
    {
        return [
            [
                '№',
                'Задача',
                '№ задачи',
                'Рассчетное время, ч',
                'Примечание',
                'Инициатор',
                'Модуль',
                'Дата исполнения',
                'Дата',
                'Статус',
                'Ссылка на Гитлаб',
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->horizontalAlign('C', \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->horizontalAlign('D', \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(5);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(40);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(120);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(50);


                $event->sheet->getStyle('E')->getAlignment()->setWrapText(true);
            }
        ];
    }



    public function collection()
    {
        $collection = collect();

        foreach ($this->tasks as $index => $task) {
            $push = [];

            $push[0] = $index + 1; // №
            $push[1] = $task->title; // Задача
            $push[2] = $task->id; // № задачи
            $push[3] = $task->estimate; // Рассчетное время, ч
            $push[4] = $task->description; // Примечание
            $push[5] = $task->sender->full_name ?? $task->sender->full_name; // Инициатор
            $push[6] = ''; // Модуль
            $push[7] = $task->solved_at ? Carbon::parse($task->solved_at)->format('d.m.Y H:i') : 'Не назначена'; // Дата исполнения
            $push[8] = $task->created_at->format('d.m.Y H:i'); // Дата
            $push[9] = $task->status ? $task->statuses[$task->status] : 'Новая'; // Статус
            $push[10] = $task->gitlab_link ?? 'Не указана'; // Ссылка на Гитлаб

            $collection->push($push);
        }


        return $collection;
    }

    public function export($fileName = 'support_tasks.xlsx')
    {
        return $this->download($fileName);
    }
}
