<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Traits\NotificationGenerator;
use Illuminate\Console\Command;

class BirthdayNotifier extends Command
{
    use NotificationGenerator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command search users who have a birthday soon and make notifications for other users about this';

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
        $whoHaveBirthdayToday = User::whoHaveBirthdayToday()->get();
        $whoHaveBirthdayNextWeek = User::whoHaveBirthdayNextWeek()->get();

        $this->generateBirthdayTodayNotifications($whoHaveBirthdayToday);
        $this->generateBirthdayNextWeekNotifications($whoHaveBirthdayNextWeek);
    }
}
