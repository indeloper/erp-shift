<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\Enum\NotificationType;
use App\Notifications\DefaultNotification;

final class NotificationData
{
    private $userId;
    private $name;
    private $description;
    private $type;
    private $data;

    public function __construct(
        int $userId,
        string $name,
        ?string $description,
        int $type,
        array $data = []
    )
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getData(): array
    {
        return $this->data;
    }

}