<?php

namespace App\Models\CommercialOffer;

use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Model;

class CommercialOfferNote extends Model
{
    use Reviewable;

    protected $fillable = ['note', 'commercial_offer_id'];
}
