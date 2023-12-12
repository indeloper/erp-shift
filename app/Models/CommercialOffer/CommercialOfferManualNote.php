<?php

namespace App\Models\CommercialOffer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommercialOfferManualNote extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
}
