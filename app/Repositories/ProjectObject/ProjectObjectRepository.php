<?php

namespace App\Repositories\ProjectObject;

use App\Models\ProjectObject;
use Illuminate\Database\Eloquent\Collection;

class ProjectObjectRepository implements ProjectObjectRepositoryInterface
{
	public function getAll(): ?Collection
	{
		return ProjectObject::all();
	}
}
