<?php

use App\Notifications\BaseNotification;
use App\Services\NotificationItem\NotificationItemServiceInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationPath = app_path('Notifications');
        $files = File::files($notificationPath);

        $notificationService = app(NotificationItemServiceInterface::class);

        foreach ($files as $file) {
            $className = 'App\\Notifications\\'.pathinfo($file, PATHINFO_FILENAME);

            if (is_subclass_of($className, BaseNotification::class)) {
                $description = constant("$className::DESCRIPTION");

                $notificationService->store(
                    $className,
                    $description,
                    true
                );
            }
        }

        //        scandir(\)
        //
        //        $oClass = new ReflectionClass(\App\Notifications\UserTestCreateNotice::class);
        //
        //        $oClass->getExtension()
        //
        //        $notificationService = app(NotificationItemServiceInterface::class);
        //
        //        $consts = $oClass->getConstants();
        //
        //        foreach ($consts as $const) {
        //            $class = NotificationType::determinateNotificationClassByType(
        //                $const
        //            );
        //
        //            $description = $class::DESCRIPTION;
        //
        //            $notificationService->store(
        //                $const,
        //                $class,
        //                $description,
        //                true
        //            );
        //        }
    }
}
