<?php

namespace App\Traits;

use App\Models\TechAcc\Defects\Defects;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Defectable
{
    public function defects(): MorphMany
    {
        return $this->morphMany(Defects::class, 'defectable');
    }

    public function defectsLight(): MorphMany
    {
        $niceStatuses = implode(',', Defects::USUALLY_SHOWING);

        return $this->morphMany(Defects::class, 'defectable')->setEagerLoads([])
            ->orderByRaw("CASE WHEN status IN ({$niceStatuses}) THEN 0 ELSE 2 END")->limit(10);
    }
}
