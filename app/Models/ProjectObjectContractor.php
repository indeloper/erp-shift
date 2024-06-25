<?php

namespace App\Models;

use App\Models\Contractors\Contractor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectObjectContractor extends Model
{

    protected $guarded = [];

    protected $casts
        = [
            'is_main' => 'boolean',
        ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

}
