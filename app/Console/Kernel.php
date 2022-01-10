<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MakeUser::class,
        Commands\MakeTestCall::class,
        Commands\CheckExTasks::class,
        Commands\CheckDelayedTasks::class,
        Commands\DBCleaner::class,
        Commands\ForceMigrationSync::class,
        Commands\TechUpdatesNotify::class,
        Commands\TechUpdatesNotifyEarlyFinished::class,
        Commands\CheckVacations::class,
        Commands\MatAccTransferBase::class,
        Commands\CheckContractorContactsAdding::class,
        Commands\SetProjectResponsibleUser::class,
        Commands\ManualContractsRemove::class,
        Commands\RemoveUnusedContacts::class,
        Commands\MoveThreadsCreatorsToNewLogic::class,
        Commands\NotifySender::class,
        Commands\DefectExpireCommand::class,
        Commands\AutoConfirmTicket::class,
        Commands\BirthdayNotifier::class,
        Commands\AppearanceControlCommand::class,
        Commands\WorkTimeControlCommand::class,
        Commands\BirthdayNotifier::class,
        Commands\CheckContractorsInfo::class,
        Commands\CreateUsageReportTask::class,
        Commands\MoveManualRodTo7Category::class,
        Commands\fixOperationOn23Jan::class,
        Commands\CertificatelessOperationsNotify::class,
        Commands\RefactorSplitsDB::class,
        Commands\GenerateEmails::class,
        Commands\ExpiredTaskReminder::class,
        Commands\CreateNewPlanMat::class,
//        Commands\SendNotificationsNeedContract::class
        // q3w custom commands
        Commands\CheckOverdueMaterialAccountingOperation::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

//        $schedule->command('check:ex-task')->everyTenMinutes();
        $schedule->command('tasks:checkDelayed')->everyThirtyMinutes();
        $schedule->command('users:check-vacations')->dailyAt('01:00');
        $schedule->command('contractors:check-contacts')->dailyAt('04:00');
        $schedule->command('contacts:check')->dailyAt('04:00');
        $schedule->command('defects:check')->dailyAt('08:00');
        $schedule->command('usage_report_task:create')->dailyAt('07:00');
        $schedule->command('expired:remind')->dailyAt('17:30');
        $schedule->command('birthday:check')->dailyAt('09:00');

        $schedule->command('ticket:auto_confirm')->everyFiveMinutes();

        $schedule->command('check:contractors')->dailyAt('01:00');
        $schedule->command('certificatless-operations:notify')->cron('0 10 * * 1-5');
        //material accounting
        $schedule->command('mat_acc:transfer_base')->dailyAt('03:15');
        // human resources
//        $schedule->command('appearance:control')->cron('0 8 * * 1-5');
//        $schedule->command('appearance:control', ['time' => '9:00'])->cron('0 9 * * 1-5');
//        $schedule->command('appearance:control', ['time' => '17:00'])->cron('0 17 * * 1-5');
//        $schedule->command('work-time:control')->cron('0 17 * * 1-5');
//        $schedule->command('work-time:control', ['time' => '21:00'])->cron('0 21 * * 1-5');
//        $schedule->command('notification:need-contract')->cron('0 0 */3 * *');
        //q3w material accounting
        $schedule->command('materialOperation:checkOverdue')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // $this->load(__DIR__.'/Commands');
        // require base_path('routes/console.php');
    }
}
