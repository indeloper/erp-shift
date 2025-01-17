<?php

declare(strict_types=1);

namespace App\Domain\DTO\ProjectObjectContractor;

use App\Domain\DTO\DTO;

final class ProjectObjectContractorData extends DTO
{

    public function __construct(
        public string $contractor_id,
        public bool $is_main
    ) {}

}