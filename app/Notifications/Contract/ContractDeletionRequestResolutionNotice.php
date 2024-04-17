<?php

namespace App\Notifications\Contract;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\TelegramNotificationData;
use App\NotificationChannels\DatabaseChannel;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractDeletionRequestResolutionNotice extends Notification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о решении задачи Запрос на удаление договора';

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
            ->markdown('mail.contract.contract-notification', [
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
