<?php

namespace App\Models\CommercialOffer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommercialOfferManualRequirement extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');
}
