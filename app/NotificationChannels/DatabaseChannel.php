<?php

declare(strict_types=1);

namespace App\NotificationChannels;

use App\Services\Notification\NotificationServiceInterface;

final class DatabaseChannel
{
    /** @var NotificationServiceInterface */
    private $notificationService;

    public function __construct(
        NotificationServiceInterface $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, $notification)
    {
        $data = $notification->toDatabase($notifiable);

        $this->notificationService->store(
            $data
        );
    }
}
