<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\DTO\ShortNameProjectObject\ShortNameProjectObjectData;
use App\Models\ProjectObject;
use App\Models\ShortNameProjectObject;
use App\Models\User;

final class ShortNameProjectObjectService
{

    public function store(
        User $editor,
        ProjectObject $projectObject,
        ShortNameProjectObjectData $data
    ): ShortNameProjectObject {
        return ShortNameProjectObject::query()->updateOrCreate([
            'project_object_id' => $projectObject->id,
        ], [
            ...$data->toArray(),
        ]);
    }

}