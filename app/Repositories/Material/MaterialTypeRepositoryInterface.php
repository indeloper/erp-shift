<?php

namespace App\Repositories\Material;

use Illuminate\Database\Eloquent\Collection;

interface MaterialTypeRepositoryInterface
{
    public function getAll(): ?Collection;
}
