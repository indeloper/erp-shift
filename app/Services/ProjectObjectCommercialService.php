<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\ProjectObject;
use Illuminate\Support\Collection;

final class ProjectObjectCommercialService
{

    public function getCommercial(ProjectObject $projectObject
    ): Collection {
        $result = collect();

        $com = CommercialOffer::where('project_id', $projectObject->project_id)
            ->orderBy('commercial_offers.version', 'desc')
            ->leftjoin('users', 'users.id', '=', 'commercial_offers.user_id')
            ->select('commercial_offers.*', 'users.last_name',
                'users.first_name', 'users.patronymic')
            ->with('get_requests')
            ->get()
            ->sortByDesc('version')
            ->groupBy(['is_tongue', 'option']);

        foreach ($com as $offer_group_by_is_tongue) {
            foreach ($offer_group_by_is_tongue as $offers_type) {
                foreach ($offers_type as $offer) {
                    $result->push($offer);
                }
            }
        }

        return $result;
    }

}