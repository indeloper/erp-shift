<?php

namespace App\Repositories\Material;

use Illuminate\Database\Eloquent\Collection;

interface MaterialAccountingTypeRepositoryInterface
{
    public function getAll(): ?Collection;
}
