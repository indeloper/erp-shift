<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'review',
        'reviewable_type',
        'result_status',
        'reviewable_id',
        'commercial_offer_id'];

    public function reviewable()
    {
        return $this->morphTo();
    }

}
