<?php

namespace App\Traits;

use App\Models\User;

trait HasAuthor
{
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
