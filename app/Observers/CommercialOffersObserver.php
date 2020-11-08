<?php

namespace App\Observers;

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Project;
use App\Traits\AdditionalFunctions;
class CommercialOffersObserver
{
    use AdditionalFunctions;

    /**
     * Handle the commercial_offer "saved" event.
     *
     * @param  CommercialOffer $commercialOffer
     * @return void
     */
    public function saved(CommercialOffer $commercialOffer)
    {
        if ($commercialOffer->isNotAgreedWithCustomer()) return;

        $this->projectImportanceLogic($commercialOffer);
    }

    /**
     * This function checks all commercial offer branches
     * and make project not important if all
     * branches in status 4 or Agreed With Customer
     * @param CommercialOffer $commercialOffer
     * @return void
     */
    public function projectImportanceLogic(CommercialOffer $commercialOffer)
    {
        $project = Project::findOrFail($commercialOffer->project_id);

        if (! $project->is_important) return;
        $projectCommercialOffers = $project->com_offers->sortByDesc('version')->groupBy('option');

        $commercialOfferStatuses = $projectCommercialOffers->map(function ($commercialOfferBranch) {
            return $commercialOfferBranch->first()->status;
        })->toArray();

        if ($this->projectCommercialOffersBranchesIsAgreedWithCustomer($commercialOfferStatuses)) {
            $project->update(['is_important' => 0]);
        }
    }

    /**
     * This function check that all Commercial Offers
     * branches is in agreed status
     * @param array $commercialOfferStatuses
     * @return bool
     */
    public function projectCommercialOffersBranchesIsAgreedWithCustomer(array $commercialOfferStatuses): bool
    {
        return $this->all_values_in_array_are(4, $commercialOfferStatuses);
    }
}
