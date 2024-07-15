<?php

namespace App\Models\CommercialOffer;

use Illuminate\Database\Eloquent\Model;

class ComOfferGantt extends Model
{
    protected $fillable = ['com_offer_id', 'gantt_image', 'order'];
}
