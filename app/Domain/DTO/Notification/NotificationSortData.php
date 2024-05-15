<?php

declare(strict_types=1);

namespace App\Domain\DTO\Notification;

final class NotificationSortData
{
    public $selector;

    public $direction;

    public function __construct(?string $selector = null, ?string $direction = null)
    {
        $this->selector = $selector;
        $this->direction = $direction;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

}