<?php

namespace App\Notifications\Contractor;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ContractorDeletionControlTaskResolutionNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о решении задачи Контроль удаления контрагента';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.contractor.contractor-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url' => $this->notificationData->getUrl(),
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
            'notifications.telegram.default-with-url'
        );
    }
}
