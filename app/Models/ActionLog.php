<?php

namespace App\Models;

use App\Traits\HasAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActionLog extends Model
{
    use HasAuthor, SoftDeletes;

    protected $fillable = ['user_id', 'actions', 'logable_type', 'logable_id'];

    protected function casts(): array
    {
        return [
            'actions' => 'array'
        ];
    }

    /**
     * Relation to logable model
     */
    public function logable(): MorphTo
    {
        return $this->morphTo();
    }
}
