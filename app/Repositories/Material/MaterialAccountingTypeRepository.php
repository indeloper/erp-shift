<?php

namespace App\Repositories\Material;

use App\Models\q3wMaterial\q3wMaterialAccountingType;
use Illuminate\Database\Eloquent\Collection;

class MaterialAccountingTypeRepository implements MaterialAccountingTypeRepositoryInterface
{
	public function getAll(): ?Collection
	{
        return q3wMaterialAccountingType::all('id', 'value');
	}
}
