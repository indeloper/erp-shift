<?php

namespace App\Repositories\ProjectObject;

use Illuminate\Database\Eloquent\Collection;

interface ProjectObjectRepositoryInterface
{
    public function getAll(): ?Collection;
}
