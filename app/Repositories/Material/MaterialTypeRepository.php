<?php

namespace App\Repositories\Material;

use App\Models\q3wMaterial\q3wMaterialType;
use Illuminate\Database\Eloquent\Collection;

class MaterialTypeRepository implements MaterialTypeRepositoryInterface
{
	public function getAll(): ?Collection
	{
        return q3wMaterialType::all('id', 'name');
	}
}
