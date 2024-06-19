<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\DTO\ShortNameProjectObject\ShortNameProjectObjectData;
use App\Models\ShortNameProjectObject;

final class ShortNameProjectObjectService
{

    public function store(ShortNameProjectObjectData $data
    ): ShortNameProjectObject {
        return ShortNameProjectObject::create($data->toArray());
    }

}