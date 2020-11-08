<?php

namespace App\Observers\CommercialOffers;

use App\Models\CommercialOffer\CommercialOfferWork;
use App\Models\Manual\ManualWork;

class CommercialOfferWorkObserver
{
    /**
     * Handle the commercial offer work "saving" event.
     *
     * @param  CommercialOfferWork  $commercialOfferWork
     * @return void
     */
    public function saving(CommercialOfferWork $commercialOfferWork)
    {
        if (!$commercialOfferWork->unit) {
            $commercialOfferWork->unit = $commercialOfferWork->manual->unit;
        }
    }
}
