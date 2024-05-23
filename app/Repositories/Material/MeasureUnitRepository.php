<?php

declare(strict_types=1);

namespace App\Repositories\Material;

use App\Models\q3wMaterial\q3wMeasureUnit;
use Illuminate\Database\Eloquent\Collection;

final class MeasureUnitRepository implements MeasureUnitRepositoryInterface
{
    public function getAll(): ?Collection
    {
        return q3wMeasureUnit::all('id', 'value');
    }
}
