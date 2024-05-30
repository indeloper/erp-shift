<?php

namespace App\Traits;

trait RussianShortDates
{
    public function initializeRussianShortDatesOnCreatedAndUpdated()
    {
        $this->casts = array_merge([
            'created_at' => 'date:d.m.Y',
            'updated_at' => 'date:d.m.Y',
            'sending_from_date' => 'date:d.m.Y',
            'sending_to_date' => 'date:d.m.Y',
            'getting_from_date' => 'date:d.m.Y',
            'getting_to_date' => 'date:d.m.Y',
            'usage_from_date' => 'date:d.m.Y',
            'usage_to_date' => 'date:d.m.Y',
        ], $this->casts);
    }
}
