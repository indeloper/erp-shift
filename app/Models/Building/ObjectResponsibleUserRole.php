<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;

class ObjectResponsibleUserRole extends Model
{
    protected $guarded = [];

    public function getRoleIdBySlug($slug)
    {
        return $this->where('slug', $slug)->firstOrFail()->id;
    }
}
