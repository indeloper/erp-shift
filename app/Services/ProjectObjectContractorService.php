<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\DTO\ProjectObjectContractor\ProjectObjectContractorData;
use App\Models\ProjectObject;
use App\Models\ProjectObjectContractor;
use App\Models\User;

final class ProjectObjectContractorService
{

    public function store(User $author, ProjectObject $projectObject, ProjectObjectContractorData $data): ProjectObjectContractor
    {
        return ProjectObjectContractor::query()->create([
            'contractor_id' => $data->contractor_id,
            'is_main' => $data->is_main,
            'project_object_id' => $projectObject->id,
            'user_id' => $author->id
        ]);
    }

}