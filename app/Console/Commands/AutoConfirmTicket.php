<?php

namespace App\Console\Commands;

use App\Models\TechAcc\OurTechnicTicket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoConfirmTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:auto_confirm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatching a job for auto confirming ticket when 20 minutes pass';

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
    public function handle(): void
    {
        $expired_time = Carbon::now()->subMinutes(20);

        $old_by_day = OurTechnicTicket::whereDate('created_at', '<', $expired_time)->where('status', 1)->get();
        $old_by_time = OurTechnicTicket::whereTime('created_at', '<', $expired_time)->where('status', 1)->get();
        $all_old = $old_by_day->merge($old_by_time)->unique();

        foreach ($all_old as $ticket) {
            \App\Jobs\AutoConfirmTicket::dispatchSync($ticket);
        }
    }
}
