<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\DTO\ProjectObjectContractor\ProjectObjectContractorData;
use App\Models\Object\ObjectContact;

final class ProjectObjectContractorService
{

    public function store(ProjectObjectContractorData $data): ObjectContact
    {
        return ObjectContact::query()->create($data->toArray());
    }

}