<?php

namespace App\Repositories\Material;

use Illuminate\Support\Collection;

interface MaterialStandardRepositoryInterface
{
    public function getAll(): ?Collection;
}
