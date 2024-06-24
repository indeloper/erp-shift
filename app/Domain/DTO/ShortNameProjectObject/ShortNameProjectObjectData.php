<?php

declare(strict_types=1);

namespace App\Domain\DTO\ShortNameProjectObject;

use App\Domain\DTO\DTO;

final class ShortNameProjectObjectData extends DTO
{

    public function __construct(
        public ?string $objectName,
        public ?string $objectCaption,
        public ?string $postalCode,
        public ?string $city,
        public ?string $street,
        public ?string $section,
        public ?string $building,
        public ?string $housing,
        public ?string $letter,
        public ?string $construction,
        public ?string $stead,
        public ?string $queue,
        public ?string $lot,
        public ?string $stage,
        public ?string $housingArea,
        public ?string $cadastralNumber,
    ) {}

}