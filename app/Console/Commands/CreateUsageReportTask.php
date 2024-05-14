<?php

namespace App\Console\Commands;

use App\Models\TechAcc\OurTechnicTicket;
use App\Services\TechAccounting\TechnicTicketReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateUsageReportTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage_report_task:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates usage report tasks for ticket usage responsible users for today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $grouped_tickets = OurTechnicTicket::where('status', 7)->get()->groupBy(function ($item) {
            return $item->users()->ofType('usage_resp_user_id')->activeResp()->first()->id ?? '-1';
        });
        $report_service = new TechnicTicketReportService();

        foreach ($grouped_tickets as $user_id => $tickets) {
            $report_service->checkAndCloseTaskForUserIdForDate($user_id, Carbon::now()->subDay());
            $report_service->checkAndCreateTaskForUserId($user_id, $tickets);
        }
    }
}
