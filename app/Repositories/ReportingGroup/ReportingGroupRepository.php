<?php

declare(strict_types=1);

namespace App\Repositories\ReportingGroup;

use App\Models\ReportingGroup;
use Illuminate\Database\Eloquent\Collection;

final class ReportingGroupRepository
{

    public function list(): Collection
    {
        return ReportingGroup::query()->get();
    }

}