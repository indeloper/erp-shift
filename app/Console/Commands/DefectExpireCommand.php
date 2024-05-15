<?php

namespace App\Console\Commands;

use App\Models\TechAcc\Defects\Defects;
use App\Traits\NotificationGenerator;
use Illuminate\Console\Command;

class DefectExpireCommand extends Command
{
    use NotificationGenerator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'defects:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command check nearly expired defects and generate some notifications for users';

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
        $expiring_defects = Defects::soonExpire()->get();

        foreach ($expiring_defects as $expiring_defect) {
            $this->generateDefectExpireNotification($expiring_defect);
        }
    }
}
