<?php

use App\Domain\Enum\NotificationType;
use App\Services\NotificationItem\NotificationItemServiceInterface;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $oClass = new ReflectionClass(NotificationType::class);

        $notificationService = app(NotificationItemServiceInterface::class);

        $consts = $oClass->getConstants();

        foreach ($consts as $const) {
            $class = NotificationType::determinateNotificationClassByType(
                $const
            );

            $description = $class::DESCRIPTION;

            $notificationService->store(
                $const,
                $class,
                $description,
                true
            );
        }
    }
}
