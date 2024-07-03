<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Model;

class ContractCommercialOfferRelation extends Model
{
    protected $fillable = ['contract_id', 'commercial_offer_id'];
}
