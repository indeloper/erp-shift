<?php

namespace App\Console\Commands;

use App\Domain\DTO\NotificationData;
use App\Domain\Enum\NotificationType;
use App\Jobs\Notification\NotificationJob;
use App\Notifications\DefaultNotification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{Notification, User};
use Illuminate\Console\Command;

class NotifySender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:send
        {text=Стандартное сообщение : Argument for notification text}
        {place=0 : Argument for send places (see self::PLACES)}
        {users=0 : Argument for users select for send (see self::USERS)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command send custom notifications in some places for some users. Enjoy! :^)';

    const PLACES = [
        0 => 'system only',
        1 => 'telegram only',
        2 => 'system and telegram',
    ];

    const USERS = [
        0 => 'users without telegram',
        1 => 'users with telegram',
        2 => 'everyone (with email)',
    ];

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
        $places = self::PLACES;
        $user_types = self::USERS;
        $text = $this->argument('text');
        $place = $this->argument('place');
        $users = $this->argument('users');

        $this->checkParameters($text, $place, $users);

        $this->generateNotifications($text, $place, $users);

        $this->info("Send notification with text: '{$text}', in {$places[$place]} for {$user_types[$users]}");
    }

    /**
     * Function check parameters and
     * can stop execution
     * @param string $text
     * @param string $place
     * @param string $users
     * @param bool $have_errors
     */
    public function checkParameters(string $text, string $place, string $users, $have_errors = false)
    {
        // check $text
        if ($text == 'Стандартное сообщение') {
            if (! $this->confirm('Looks like you dont send any text here. Do you want to continue?')) { $have_errors = true; }
        }
        // check $place
        if (! in_array($place, array_keys(self::PLACES))) { $this->error('Bad $place parameter value! Check inputs'); $have_errors = true; }

        // check $users
        if (! in_array($users, array_keys(self::USERS))) { $this->error('Bad $users parameter value! Check inputs'); $have_errors = true; }

        if ($have_errors) exit(5);
    }

    /**
     * Function create notifications with
     * $text to $recipients
     * @param string $text
     * @param string $place
     * @param string $users
     */
    public function generateNotifications(string $text, string $place, string $users)
    {
        $recipients = User::query()
            ->when($users === 0, function (Builder $query) {
                $query->withoutTelegramChatId();
            })
            ->when($users === 1, function (Builder $query) {
                $query->withTelegramChatId();
            })
            ->get();

        $recipients->each(function (User $user) use ($text) {
            NotificationJob::dispatchNow(
                new NotificationData(
                    $user->id,
                    $text,
                    'Описание уведомления',
                    NotificationType::DEFAULT
                )
            );

//            NotificationJob::dispatchNow(
//                new NotificationData(
//                    $user->id,
//                    $text,
//                    'Описание уведомления',
//                    NotificationType::ONLY_TELEGRAM,
//                    [
//                        'name' => $user->first_name
//                    ]
//                )
//            )->onQueue('notify');
        });


    }
}
