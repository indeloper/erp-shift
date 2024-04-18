<?php

namespace App\Notifications\CommercialOffer;

use App\Domain\DTO\NotificationData;
use App\Domain\DTO\TelegramNotificationData;
use App\NotificationChannels\DatabaseChannel;
use App\NotificationChannels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferProcessingNotice extends Notification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление об обработке заявки на КП';

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
            ->markdown('mail.commercial_offer.commercial-offer-notification', [
                'name' => $this->notificationData->getName(),
                'link' => $this->notificationData->getAdditionalInfo(),
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
