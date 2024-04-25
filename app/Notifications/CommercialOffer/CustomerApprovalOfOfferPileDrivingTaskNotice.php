<?php

namespace App\Notifications\CommercialOffer;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerApprovalOfOfferPileDrivingTaskNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о создании задачи Согласование КП с заказчиком (свайное направление)';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->notificationData->getDescription())
            ->markdown('mail.commercial_offer.commercial-offer-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url'  => $this->notificationData->getUrl(),
                'description' => $this->notificationData->getDescription(),
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
            'telegram.commercial.customer-approval-of-offer-pile-driving-task'
        );
    }
}
