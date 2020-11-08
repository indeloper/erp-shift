<?php

namespace App\Models\Contract;

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Project;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractCommercialOfferRelation extends Model
{
    protected $fillable= ['contract_id', 'commercial_offer_id'];

}
