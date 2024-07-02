<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProjectObject;
use App\Models\ProjectObjectContact;

final class ProjectObjectContactService
{

    public function store(
        ProjectObject $projectObject,
        int $contactId,
        string $note
    ): ProjectObjectContact {
        return ProjectObjectContact::query()->create([
            'project_object_id' => $projectObject->id,
            'contact_id'        => $contactId,
            'note'              => $note,
        ]);
    }

}