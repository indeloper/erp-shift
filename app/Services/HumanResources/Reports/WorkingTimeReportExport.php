<?php


namespace App\Services\HumanResources\Reports;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WorkingTimeReportExport implements WithMultipleSheets
{
    use Exportable;

    private $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function sheets() :array
    {
        $sheets = [];

        $report_groups = $this->users->groupBy('user.reportGroup.id');

        //remove users without reportGroup
        unset($report_groups['']);

        foreach($report_groups as $group) {
            $sheets[] = new ReportGroupSheet($group);
        }

        return $sheets;
    }

    public function export($fileName = 'report.xlsx')
    {
        return $this->download($fileName);
    }
}
