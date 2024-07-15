<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class CheckExperiencePersonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:experience-person';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяем стаж работы и отпрвляем уведомление!';

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
        User::query()
            ->where('status', '=', 1)
            ->where('is_deleted', '=', 0)
            ->chunk(100, function ($users) {
                foreach ($users as $user) {

                    if (now()->setTime(0, 0)->diffInDays($user->experience->setTime(0, 0)) % 365 !== 0) {
                        continue;
                    }

                    Notification::query()->create([
                        'name' => view('telegram.experience-message', compact('user'))->render(),
                        'user_id' => $user->id,
                        'type' => 112,
                    ]);

                    sleep(1);
                }
            });
    }
}
