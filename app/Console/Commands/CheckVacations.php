<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vacation\VacationsHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckVacations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-vacations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run vacation logic. Can send user on vacation or take him out from vacation';

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
        DB::beginTransaction();

        $now = Carbon::now();

        $vacations = VacationsHistory::where('is_actual', 1)->get();

        foreach ($vacations as $vacation) {
            $user_vacation = $vacation->user_vacation_status();
            if ($now->greaterThanOrEqualTo(Carbon::parse($vacation->from_date)) and $user_vacation == 0) {
                $in_vacation = User::to_vacation($vacation->vacation_user_id, $vacation);
            } elseif ($now->greaterThanOrEqualTo(Carbon::parse($vacation->by_date)) and $user_vacation == 1) {
                $from_vacation = User::from_vacation($vacation->vacation_user_id, $vacation);
            }
        }

        DB::commit();
    }
}
