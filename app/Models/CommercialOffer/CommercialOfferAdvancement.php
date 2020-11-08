<?php

namespace App\Models\CommercialOffer;

use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Model;

class CommercialOfferAdvancement extends Model
{
    use Reviewable;

    protected $fillable = ['value', 'is_percent', 'commercial_offer_id', 'description'];
}
