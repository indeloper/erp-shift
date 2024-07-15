<?php

namespace App\Services\Commerce;

use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\OurTechnic;
use Illuminate\Support\Facades\DB;

class ProjectDashboardService
{
    public function collectStats(Project $project)
    {
        $data = [];

        //work volumes
        $data['work_volumes'] = $this->workVolumeStats($project);

        //materials
        $data['materials'] = $this->materialStats($project, $data['work_volumes']);

        //com offers
        $data['com_offers'] = $this->comOfferStats($project);

        //technics with status and ticket/defect link
        $data['technics'] = $this->technicStats($project);

        //human resources
        //Looking forward for human resources update

        //contract
        $data['contract'] = $this->contractStats($project);
        $data['work_volumes']->each(function ($wv) {
            unset($wv->materials);
        });
        $data['project'] = [
            'id' => $project->id,
            'name' => $project->name,
            'link' => route('projects::card', $project->id),
            'object' => $project->object,
            'contractor' => $project->contractor,
        ];
        $data['object'] = [
            'id' => $project->object_id,
            'name' => $project->object->name_tag,
            'link' => route('building::mat_acc::report_card', [
                'parameter_id' => 0,
                'value_ids' => $project->object_id,
            ]),
        ];

        return $data;
    }

    public function workVolumeStats(Project $project)
    {
        $wvs = $project->work_volumes()
            ->orderBy('version', 'desc')
            ->groupBy('type', 'option')
            ->select('work_volumes.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        return $wvs;
    }

    public function comOfferStats(Project $project)
    {
        $com_offers = $project->com_offers()
            ->whereHas('work_volume', function ($q) {
                $q->where('status', 2);
            })
            ->orderBy('version', 'desc')
            ->get()
            ->groupBy('option')
            ->reduce(function ($carry, $item) {
                $last_offer = $item->first();
                $carry->push($last_offer);

                return $carry;
            }, collect());

        return $com_offers;
    }

    public function contractStats(Project $project)
    {
        return $project->ready_contracts()->get();
    }

    public function technicStats(Project $project)
    {
        $project_object_id = $project->object_id;

        $technics = OurTechnic::where('start_location_id', $project_object_id)
            ->with(
                ['tickets' => function ($tic_q) {
                    $tic_q->whereNotIn('status', [3, 8])->take(1);
                }],
                ['defects' => function ($def_q) {
                    $def_q->whereNotIn('status', Defects::USUALLY_HIDING)->take(1);
                }])
            ->get();

        return $technics;
    }

    public function materialStats(Project $project, \Illuminate\Database\Eloquent\Collection $work_volumes)
    {
        /** @var ProjectObject $object */
        $object = $project->object()->first();

        $project_mats = $work_volumes->pluck('materials')->flatten()->filter(function ($mat) {
            return $mat->material_type == 'regular';
        });
        //here we have count in category unit
        $project_mats_stats = $project_mats->reduce(function ($grouped_sum, $mat) {
            $cat_id = $mat->manual->category_id;
            if (isset($grouped_sum[$cat_id])) {
                $grouped_sum[$cat_id]['sum'] += $mat->count;
                $grouped_sum[$cat_id]['unit'] = $mat->unit;
            } else {
                $grouped_sum[$cat_id] = [
                    'name' => $mat->manual->category->name,
                    'sum' => $mat->count,
                    'unit' => $mat->unit,
                ];
            }

            return $grouped_sum;
        });

        $mat_acc_materials = MaterialAccountingBase::where('object_id', $object->id)->where('transferred_today', 0)->with('material')->get();

        //TODO: check count unit here
        $mat_acc_stats = $mat_acc_materials->reduce(function ($grouped_sum, $mat) {
            if ($mat->unit != 'т') {
                $mat->count = ($mat->convert_params->where('unit', 'т')->first()->value ?? 0) * $mat->count;
                $mat->unit = 'т';
            }
            $cat_id = $mat->material->category_id;
            if (isset($grouped_sum[$cat_id])) {
                $grouped_sum[$cat_id]['sum'] += round($mat->count, 3);
                $grouped_sum[$cat_id]['unit'] = $mat->unit;

            } else {
                $grouped_sum[$cat_id] = [
                    'name' => $mat->material->category->name,
                    'sum' => round($mat->count, 3),
                    'unit' => $mat->unit,
                ];
            }

            return $grouped_sum;
        });

        $cat_percentage = [];
        if ($project_mats_stats) {
            foreach ($project_mats_stats as $cat_id => $proj_sum) {
                $mat_acc_sum = $mat_acc_stats[$cat_id] ?? 0;
                $percent = (1 - (($proj_sum['sum'] - $mat_acc_sum) / $proj_sum['sum']));
                $cat_percentage[$cat_id] = [
                    'name' => $proj_sum['name'],
                    'sum' => $percent > 1 ? 1 : $percent,
                    'count' => round($mat_acc_sum, 3),
                    'wv_count' => round($proj_sum['sum'], 3),
                    'unit' => $mat_acc_sum,
                ];
            }
        }
        if ($mat_acc_stats) {
            foreach ($mat_acc_stats as $cat_id => $mat_acc_sum) {
                $proj_sum = $project_mats_stats[$cat_id] ?? 0;
                if (! isset($cat_percentage[$cat_id])) {
                    $cat_percentage[$cat_id] = [
                        'name' => $mat_acc_sum['name'],
                        'sum' => 1,
                        'count' => round($mat_acc_sum['sum'], 3),
                        'wv_count' => round($proj_sum, 3),
                        'unit' => $mat_acc_sum['unit'],
                    ];
                }
            }
        }

        return array_values($cat_percentage);
    }
}
