<?php

declare(strict_types=1);

namespace App\Repositories\Material;

use Illuminate\Database\Eloquent\Collection;

interface MeasureUnitRepositoryInterface
{
    public function getAll(): ?Collection;
}
