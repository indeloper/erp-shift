<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeErpNotification extends Command
{
    protected $signature = 'make:erp-notification {name?} {description?}';

    protected $description = 'Create an ERP notification with the given class name and description';

    protected $className;

    protected $bladeName;

    protected $notificationDescription;

    public function handle()
    {
        $name = $this->argument('name');
        $description = $this->argument('description');

        if (! $name) {
            $name = $this->ask('Введите имя для класса уведомления');
        }

        if (! $description) {
            $description = $this->ask('Введите описание уведомления');
        }

        $this->className = Str::studly($name);
        $this->bladeName = Str::kebab($name);
        $this->notificationDescription = $description;

        // Создание класса уведомления
        $this->createNotificationClass();

        // Создание Blade шаблонов
        $this->createBladeViews();

        Artisan::call('db:seed --class=NotificationSeeder');
    }

    protected function createNotificationClass()
    {
        $notificationPath = app_path("Notifications/{$this->className}.php");

        $classContent = $this->getStub('erp-class-notify.stub');
        $classContent = str_replace(
            ['{{ className }}', '{{ bladeName }}', '{{ description }}'],
            [$this->className, $this->bladeName, $this->notificationDescription],
            $classContent
        );

        File::put($notificationPath, $classContent);
        $this->info("Notification class created: {$notificationPath}");
    }

    protected function createBladeViews()
    {
        $mailBladePath = resource_path("views/notifications/mail/{$this->bladeName}-notification.blade.php");
        $telegramBladePath = resource_path("views/notifications/telegram/{$this->bladeName}-notification.blade.php");

        $mailBladeContent = $this->getStub('erp-mail-blade.stub');
        $telegramBladeContent = $this->getStub('erp-telegram-blade.stub');

        File::put($mailBladePath, $mailBladeContent);
        $this->info("Mail blade view created: {$mailBladePath}");

        File::put($telegramBladePath, $telegramBladeContent);
        $this->info("Telegram blade view created: {$telegramBladePath}");
    }

    protected function getStub($filename)
    {
        return file_get_contents(__DIR__.'/Generators/stubs/notification/'.$filename);
    }
}
