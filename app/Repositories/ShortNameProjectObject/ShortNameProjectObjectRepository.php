<?php

declare(strict_types=1);

namespace App\Repositories\ShortNameProjectObject;

use App\Models\ShortNameProjectObject;

final class ShortNameProjectObjectRepository
{

    /**
     * @param  int  $projectObjectId
     *
     * @return \App\Models\ShortNameProjectObject|null
     */
    public function getByProjectObjectId(int $projectObjectId
    ): ?ShortNameProjectObject {
        return ShortNameProjectObject::query()
            ->where('project_object_id', $projectObjectId)
            ->first();
    }

}