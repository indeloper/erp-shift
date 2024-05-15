<?php

namespace App\Observers\CommercialOffers;

use App\Models\CommercialOffer\CommercialOfferMaterialSplit;

class CommercialOfferMaterialSplitObserver
{
    /**
     * Handle the commercial offer material split "saving" event.
     *
     * @param  CommercialOfferMaterialSplit  $commercialOfferMaterialSplit
     * @return void
     */
    public function saving(CommercialOfferMaterialSplit $split): void
    {
        if (! $split->unit) {
            $split->unit = $split->WV_material->manual->category_unit ?? 'т';
        }
    }
}
