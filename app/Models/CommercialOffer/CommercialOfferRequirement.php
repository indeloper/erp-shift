<?php

namespace App\Models\CommercialOffer;

use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Model;

class CommercialOfferRequirement extends Model
{
    use Reviewable;

    protected $fillable = ['requirement', 'commercial_offer_id'];
}
