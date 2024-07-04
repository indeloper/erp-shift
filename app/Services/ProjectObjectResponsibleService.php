<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProjectObject;
use App\Models\ProjectObjectContact;
use App\Models\ProjectObjectResponsible;

final class ProjectObjectResponsibleService
{

    public function store(
        ProjectObject $projectObject,
        int $contactId,
        string $note
    ): ProjectObjectContact {
        return ProjectObjectResponsible::query()->create([
            'project_object_id' => $projectObject->id,
            'contact_id'        => $contactId,
            'note'              => $note,
        ]);
    }

}