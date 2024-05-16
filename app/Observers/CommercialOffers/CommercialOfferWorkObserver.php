<?php

namespace App\Observers\CommercialOffers;

use App\Models\CommercialOffer\CommercialOfferWork;

class CommercialOfferWorkObserver
{
    /**
     * Handle the commercial offer work "saving" event.
     */
    public function saving(CommercialOfferWork $commercialOfferWork): void
    {
        if (! $commercialOfferWork->unit) {
            $commercialOfferWork->unit = $commercialOfferWork->manual->unit;
        }
    }
}
