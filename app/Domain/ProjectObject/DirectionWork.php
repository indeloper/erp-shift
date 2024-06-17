<?php

declare(strict_types=1);

namespace App\Domain\ProjectObject;

enum DirectionWork: string
{

    case Piles = 'piles'; // Сваи
    case SheetPile = 'sheet_pile'; // Шпунт

}
