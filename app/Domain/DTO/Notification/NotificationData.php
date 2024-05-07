<?php

declare(strict_types=1);

namespace App\Domain\DTO\Notification;

use Illuminate\Support\Collection;

final class NotificationData
{
    private $userId;
    private $name;
    private $class;
    private $data;

    /**
     * @var Collection
     */
    private $withoutChannels;

    /**
     * @param  int  $userId
     * @param  string  $class
     * @param  array  $data
     */
    public function __construct(
        int $userId,
        string $class,
        array $data = []
    )
    {
        $this->userId = $userId;
        $this->class = $class;
        $this->data = $data;

        $this->name = $this->getName();

        $this->withoutChannels = collect();
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getClass(): string
    {
        return $this->class;
    }

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
     * @param  Collection $withoutChannels
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
