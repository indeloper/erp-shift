<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'review',
        'reviewable_type',
        'result_status',
        'reviewable_id',
        'commercial_offer_id'];

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
}
