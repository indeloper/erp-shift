<?php

use App\Services\ProjectObjectDocuments\Notifications\ProjectObjectDocumentsNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


//        Schedule::command('check:ex-task')->everyTenMinutes();
Schedule::command('tasks:checkDelayed')->everyThirtyMinutes();
Schedule::command('users:check-vacations')->dailyAt('01:00');
Schedule::command('contractors:check-contacts')->dailyAt('04:00');
Schedule::command('contacts:check')->dailyAt('04:00');
Schedule::command('defects:check')->dailyAt('08:00');
Schedule::command('usage_report_task:create')->dailyAt('07:00');
Schedule::command('birthday:check')->dailyAt('09:00');

Schedule::command('ticket:auto_confirm')->everyFiveMinutes();

Schedule::command('check:contractors')->dailyAt('01:00');
Schedule::command('certificatless-operations:notify')->cron('0 10 * * 1-5');
//material accounting
Schedule::command('mat_acc:transfer_base')->dailyAt('03:15');
//q3w material accounting
Schedule::command('materialOperation:checkOverdue')->everyThirtyMinutes();

(new ProjectObjectDocumentsNotifications)->handle();

Schedule::command('fuelTank:notifyAboutMovingConfirmationDelay')->dailyAt('09:09');

Schedule::command("check:fuelTanksFuelLevel --dateFrom='01-01-2024'")->dailyAt('08:00');
