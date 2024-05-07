<?php

namespace App\Notifications\Task;

use App\Domain\DTO\RenderTelegramNotificationData;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class NewTasksFromDeletedUserNotice extends BaseNotification
{
    use Queueable;

    const DESCRIPTION = 'Уведомление о новых задачах от удаленного пользователя';

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(self::DESCRIPTION)
            ->markdown('notifications.mail.task.new-task-from-deleted-used-notification', [
                'name' => $this->notificationData->getName(),
                'info' => $this->notificationData->getAdditionalInfo(),
                'url'  => $this->notificationData->getUrl(),
                'tasks_url'   => $this->notificationData->getData()['tasks_url'],
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
            'notifications.telegram.task.new-task-from-deleted-user-notification'
        );
    }
}
