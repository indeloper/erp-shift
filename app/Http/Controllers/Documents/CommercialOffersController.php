<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommercialOffersController extends Controller
{
    public function index(Request $request)
    {
        $nice_statuses = implode(',', CommercialOffer::NICE_STATUSES);

        $com_offers = CommercialOffer::forDocuments();
        $material_names = [];
        $mat_names = [];
        if ($request->has(['search', 'values', 'parameters']) and $request->search) {
            $search = explode(',', $request->search);
            $values = explode(',', $request->values);

            $old_request = $request->all();

            foreach ($search as $key => $iter) {
                if (in_array($iter, ['commercial_offers.updated_at', 'commercial_offers.status', 'projects.entity'])) {
                    if ($iter == 'commercial_offers.updated_at') {
                        $dates = explode('|', $values[$key]);
                        $from = Carbon::createFromFormat('d.m.Y', $dates[0])->toDateString();
                        $to = Carbon::createFromFormat('d.m.Y', $dates[1])->toDateString();
                        $com_offers->whereDate($iter, '>=', $from)->whereDate($iter, '<=', $to);
                    } elseif ($iter == 'commercial_offers.status') {
                        $search = mb_strtolower($values[$key]);
                        $result = array_filter($com_offers->getModel()->com_offer_status, function ($item) use ($search) {
                            return stristr(mb_strtolower($item), $search);
                        });

                        $com_offers->WhereIn($iter, array_keys($result));
                    } elseif ($iter == 'projects.entity') {
                        $search = mb_strtolower($values[$key]);
                        $result = array_filter(Project::$entities, function ($item) use ($search) {
                            return stristr(mb_strtolower($item), $search);
                        });

                        $com_offers->whereIn($iter, array_keys($result));
                    }
                } elseif ($iter == 'material') {
                    $mat_names[] = $values[$key];
                } else {
                    $com_offers->where($iter, 'like', '%'.$values[$key].'%');
                }
            }

            if ($mat_names) {
                $com_offers->where(function ($query) use ($mat_names) {
                    foreach ($mat_names as $name) {
                        $query->orWhereHas('work_volume.materials', function ($work_volume) use ($name) {
                            $work_volume->whereHasMorph('manual', ['App\Models\Manual\ManualMaterial'], function ($mat) use ($name) {
                                $mat->where('name', 'like', '%'.$name.'%')
                                    ->where('material_type', 'regular');
                            });
                        });
                    }
                });

            }

        }

        $com_offers->orderByRaw("CASE WHEN is_important = 1 and commercial_offers.status IN ({$nice_statuses}) THEN 0 ELSE 2 END, id DESC");

        return view('commercial_offers.index', [
            'com_offers' => $com_offers->paginate(20),
            'entities' => Project::$entities,
            'old_request' => isset($old_request) ? $old_request : [],
            'material_names' => $material_names,
        ]);
    }
}
