<?php

declare(strict_types=1);

namespace App\Repositories\ProjectObject;

use App\Models\ProjectObject;

class ProjectObjectRepository
{

    public function getProjectObjectByBitrixId(int $bitrixId): ?ProjectObject
    {
        return ProjectObject::query()
            ->where('bitrix_id', $bitrixId)
            ->first();
    }

}