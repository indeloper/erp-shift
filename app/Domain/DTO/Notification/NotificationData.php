<?php

declare(strict_types=1);

namespace App\Domain\DTO\Notification;

use Illuminate\Support\Collection;

final class NotificationData
{
    private $userId;
    private $name;
    private $description;
    private $type;
    private $data;

    /**
     * @var Collection
     */
    private $withoutChannels;

    /**
     * @param  int  $userId
     * @param  string  $name
     * @param  string|null  $description
     * @param  int  $type
     * @param  array  $data
     */
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

        $this->withoutChannels = collect();
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

    /**
     * @return Collection
     */
    public function getWithoutChannels()
    {
        return $this->withoutChannels;
    }

    /**
     * @param  Collection  $withoutChannels
     */
    public function setWithoutChannels(Collection $withoutChannels): void
    {
        $this->withoutChannels = $withoutChannels;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->data['additional_info'] ?? null;
    }

    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }
}
