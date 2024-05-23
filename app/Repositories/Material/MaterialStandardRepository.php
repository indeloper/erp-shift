<?php

namespace App\Repositories\Material;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MaterialStandardRepository implements MaterialStandardRepositoryInterface
{
	public function getAll(): ?Collection
    {
		return DB::table('q3w_material_standards as a')
            ->leftJoin('q3w_material_types as b', 'a.material_type', '=', 'b.id')
            ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
            ->get(['a.*', 'b.name as material_type_name', 'b.measure_unit', 'b.accounting_type', 'd.value as measure_unit_value']);
	}
}
