<?php

namespace App\Traits;

use App\Models\Project;

trait Appointmentable
{
    public function appointments()
    {
        return $this->morphToMany(Project::class, 'appointmentable', 'appointments')->whereNull('appointments.deleted_at')->withTimestamps();
    }
}
