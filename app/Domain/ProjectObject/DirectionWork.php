<?php

declare(strict_types=1);

namespace App\Domain\ProjectObject;

enum DirectionWork: string
{

    case Piles = 'piles'; // Сваи
    case SheetPile = 'sheet_pile'; // Шпунт

    public function name(): ?string
    {
        return match ($this) {
            self::Piles => 'Сваи',
            self::SheetPile => 'Шпунт',
            default => null,
        };
    }

}
