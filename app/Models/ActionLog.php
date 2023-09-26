<?php

namespace App\Models;

use App\Traits\HasAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActionLog extends Model
{
    use SoftDeletes, HasAuthor;

    protected $fillable = ['user_id', 'actions', 'logable_type', 'logable_id'];

    protected $casts = ['actions' => 'array'];

    /**
     * Relation to logable model
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function logable()
    {
        return $this->morphTo();
    }
}
