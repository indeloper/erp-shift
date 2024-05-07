<?php

namespace App\Notifications\CommercialOffer;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class PileDrivingOfferApprovalTaskCreationNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о создании задачи Согласование КП (свайное направление)';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.commercial_offer.commercial-offer-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url'  => $this->notificationData->getUrl(),
            ]);
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationData;
    }

    public function toTelegram($notifiable)
    {
        return new RenderTelegramNotificationData(
            $this->notificationData,
            'notifications.telegram.commercial.offer-approve'
        );
    }
}
