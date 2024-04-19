<?php

declare(strict_types=1);

namespace App\Domain\DTO\Notification;

final class NotificationSettingsData
{
    /** @var NotificationSettingsItemData[]  */
    private $items;

    public function __construct(array $items) {
        foreach ($items as $item) {
            $this->items[] = new NotificationSettingsItemData(
                $item['id'],
                $item['mail'],
                $item['telegram'],
                $item['system']
            );
        }
    }

    /**
     * @return NotificationSettingsItemData[]|array
     */
    public function getItems(): array
    {
        return $this->items;
    }

}