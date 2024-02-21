<?php

namespace App\Http\Controllers\Timesheet;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Employees\Employee;
use App\Models\Timesheet\TimesheetCard;
use App\Models\Timesheet\TimesheetDayCategory;
use App\Models\Timesheet\TimesheetEmployeesSummaryHour;
use App\Services\Common\FileSystemService;
use App\Services\SystemService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimesheetCardsController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->baseModel = new TimesheetCard();
        $this->routeNameFixedPart = 'timesheet::timesheet-card::';
        $this->sectionTitle = 'Табель учета рабочего времени';
        $this->baseBladePath = resource_path() . '/views/timesheet/time-card';

        $this->isMobile = is_dir($this->baseBladePath . '/mobile') && SystemService::determineClientDeviceType($_SERVER["HTTP_USER_AGENT"]) === 'mobile';

        $this->componentsPath = $this->isMobile ? $this->baseBladePath . '/mobile/components' : $this->baseBladePath . '/desktop/components';

        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
        $this->modulePermissionsGroups = [];
    }

    public function index(Request $request)
    {
        $timeCardMonth = $request->get('month', Carbon::now()->month);
        $timeCardYear = $request->get('year', Carbon::now()->year);
        $employeeID = $request->get('employee-id', Employee::orderBy('employee_1c_name')->first()->id);

        $timesheetCard = TimesheetCard::where('month', $timeCardMonth)->where('year', $timeCardYear)->where('employee_id', $employeeID)->first();

        if (!$timesheetCard) {
            $timesheetCard = new TimesheetCard(['employee_id' => $employeeID, 'month' => $timeCardMonth, 'year' => $timeCardYear,]);

            $timesheetCard->save();
        }

        $data = [];
        $daysInMonthCount = $this->getLastDateOfMonth($timeCardMonth, $timeCardYear);
        $timesheetHeaderRow = ['id' => Str::uuid(), 'rowType' => "timesheetHeader", 'tariffName' => null, 'dealMultiplier' => null, 'daysInMonthCount' => $daysInMonthCount];

        for ($i = 1; $i <= $daysInMonthCount; $i++) {
            $timeCardMonthWithLeadingZero = $timeCardMonth < 10 ? "0{$timeCardMonth}" : $timeCardMonth;
            $dayWithLeadingZero = $i < 10 ? "0{$i}" : $i;

            $timesheetHeaderRow["{$timeCardYear}-{$timeCardMonthWithLeadingZero}-{$dayWithLeadingZero}"] = $i;
        }

        $data[] = $timesheetHeaderRow;

        $summaryHoursData = TimesheetEmployeesSummaryHour::where('timesheet_card_id', $timesheetCard->id)->leftJoin('timesheet_day_categories', 'timesheet_day_categories.id', '=', 'timesheet_employees_summary_hours.timesheet_day_category_id')->orderBy('date')->get()->toArray();

        $transposedSummaryHoursData = ['id' => Str::uuid(), 'timeCardId' => $timesheetCard->id, 'rowType' => "timesheetSummaryHours", 'tariffName' => 'Сумма часов', 'dealMultiplier' => null, 'daysInMonthCount' => $daysInMonthCount];

        foreach ($summaryHoursData as $row) {
            $date = $row['date'];
            $transposedSummaryHoursData[$date] = !empty($row['count']) ? $row['count'] : $row['short_name'];
        }

        $data[] = $transposedSummaryHoursData;

        $timesheetDelimiter = ['id' => Str::uuid(), 'rowType' => "timesheetDelimiter", 'caption' => "Часы"];
        $data[] = $timesheetDelimiter;

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function getLastDateOfMonth($month, $year): int
    {
        return Carbon::parse("{$year}-{$month}-01")->endOfMonth()->day;
    }

    public function update(Request $request, int $id)
    {
        DB::beginTransaction();

        $id = json_decode($id);
        $data = json_decode($request->input('data'), true);

        switch ($id->rowType) {
            case "timesheetSummaryHours":
                $this->updateTimesheetSummaryHours($id->timeCardId, $data);
                break;
        }

        DB::commit();

        return response()->json(['result' => 'ok', 'data' => $data]);
    }

    public function updateTimesheetSummaryHours(int $timeCardId, array $data)
    {
        $dayCategories = TimesheetDayCategory::all();

        foreach ($data as $date => $value) {
            $hourValue = null;
            $categoryValue = null;

            if (is_numeric($value)) {
                $hourValue = $value;
            } else {
                $dayCategoryItem = $dayCategories->first(function ($item) use ($value) {
                    return mb_strtoupper($item->short_name) === mb_strtoupper($value);
                });

                if ($dayCategoryItem) {
                    $categoryValue = $dayCategoryItem->id;
                }
            }

            TimesheetEmployeesSummaryHour::updateOrCreate(['timesheet_card_id' => $timeCardId, 'date' => $date], ['timesheet_day_category_id' => $categoryValue, 'count' => $hourValue]);
        }
    }
}
