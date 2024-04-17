<?php

namespace App\Notifications\Operation;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\TelegramNotificationData;
use App\NotificationChannels\DatabaseChannel;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WriteOffOperationRejectionNotice extends Notification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление об отклонении операции списания';

    private $notificationData;

    public function __construct(NotificationData $notificationData)
    {
        $this->notificationData = $notificationData;
    }

    public function via($notifiable)
    {
        return [
            'mail',
            DatabaseChannel::class,
            TelegramChannel::class,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->notificationData->getDescription())
            ->markdown('mail.operation.operation-notification', [
                'name' => $this->notificationData->getName(),
                'description' => $this->notificationData->getDescription(),
            ]);
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData;
    }

    public function toTelegram($notifiable)
    {
        return new TelegramNotificationData(
            $this->notificationData
        );
    }
}
