<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\Manual\ManualNodeMaterials;
use App\Models\Manual\ManualNodes;
use App\Models\Manual\ManualWork;
use App\Models\WorkVolume\WorkVolume;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeWork;
use App\Models\WorkVolume\WorkVolumeWorkMaterial;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WVCalvulatorController extends Controller
{
    public function create_tongue_calc(Request $request, $work_volume_id)
    {
        DB::beginTransaction();

        $work_volume = WorkVolume::where('id', $work_volume_id);

        $work_volume_works = WorkVolumeWork::where('work_volume_id', $work_volume_id)->pluck('manual_work_id')->toArray();
        $worksWithCount = (new ManualWork())->worksWithCount();

        $materials = ManualMaterial::whereIn('id', [$request->tongue_type, $request->type_angle])
            ->with('parameters')
            ->get();

        $tongue_material = $materials->where('category_id', 2)->first();
        $angle_material = $materials->where('category_id', 10)->first();

        $category_tongue_unit = $tongue_material->category->category_unit;

        $new_work_tongue = '';
        $new_work_angle = 0;

        if ($request->project_count >= $request->tongue_count) {
            $tongue_count = $request->project_count;
        } else {
            $tongue_count = isset($request->is_required_count) ? $request->project_count : $request->tongue_count;
        }

        $length_tongue = $materials->where('id', $request->tongue_type)->first()->parameters->where('unit', 'м')->first()->value ?? 0;

        if ($request->type_angle) {
            $length_angle = $materials->where('id', $request->type_angle)->first()->parameters->where('unit', 'м.п')->first()->value;

            $new_angle_unit_value = $angle_material->convert_to('т');
            if ($new_angle_unit_value) {
                $result_angle_count = $new_angle_unit_value->value * $request->count_angle;
            } else {
                return back();
            }
            $result_angle_count = $result_angle_count * $request->multiplier;
        }

        // check category unit and if it need convert to quantity
        if ($category_tongue_unit != 'шт') {
            $convertation_parameter = $tongue_material->convert_to('шт')->value;
            $result_tongue_count = $tongue_count / $convertation_parameter;
        } else {
            $result_tongue_count = $tongue_count;
        }
        $new_tongue_unit_value = $tongue_material->convert_to('т');
        if ($new_tongue_unit_value) {
            $result_tongue_count = $new_tongue_unit_value->value * $result_tongue_count;
        } else {
            return back();
        }

        $result_tongue_count = $result_tongue_count * $request->multiplier;
        $new_work_tongue = WorkVolumeMaterial::create([
            'user_id' => Auth::user()->id,
            'work_volume_id' => $work_volume_id,
            'manual_material_id' => $request->tongue_type,
            'is_our' => 1,
            'count' => $result_tongue_count,
            'is_tongue' => 1,
            'price_per_one' => $materials->where('id', $request->tongue_type)->first()->buy_cost,
            'result_price' => $result_tongue_count * $materials->where('id', $request->tongue_type)->first()->buy_cost,
            'unit' => 'т',
        ]);

        if ($request->count_angle and $request->type_angle) {
            $new_work_angle = WorkVolumeMaterial::create([
                'user_id' => Auth::user()->id,
                'work_volume_id' => $work_volume_id,
                'manual_material_id' => $request->type_angle,
                'is_our' => 1,
                'count' => $result_angle_count,
                'is_tongue' => 1,
                'price_per_one' => $materials->where('id', $request->type_angle)->first()->buy_cost,
                'result_price' => $request->count_angle * $materials->where('id', $request->type_angle)->first()->buy_cost,
                'unit' => 'т',
            ]);
        }

        $manual_material = WorkVolumeMaterial::whereIn('work_volume_materials.id', [$new_work_tongue->id])
            ->with('manual.parameters')
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('work_volume_materials.*', 'manual_material_categories.name', 'manual_material_categories.category_unit', 'manual_materials.category_id')
            ->get();

        $manual_works = ManualWork::whereIn('id', [4, 6, 9, 10, 15, 16, 19, 20, 27, 28])->get();

        $count_work_1 = [];

        foreach ($manual_works as $work) {
            $count_work_1[$work->id.$work->name] = 0;
        }

        $count_work_2 = $count_work_1;

        foreach ($manual_material as $material) {
            foreach ($manual_works as $work) {
                if ($material->category_unit == $work->unit) {
                    $count_work_1[$work->id.$work->name] += round($material->count / $new_tongue_unit_value->value, 3);
                }
            }
        }

        foreach ($manual_material as $material) {
            foreach ($material->parameters as $parameter) {
                foreach ($manual_works as $work) {
                    if ($parameter->unit == $work->unit) {
                        $count_work_2[$work->id.$work->name] += round($material->count, 3);
                    }
                }
            }
        }

        foreach ($count_work_1 as $key => $value) {
            if ($value == 0) {
                unset($count_work_1[$key]);
            }
        }

        $work_counts_tongue = array_values(array_merge($count_work_2, $count_work_1));

        if ($request->count_angle and $request->type_angle) {
            $manual_material = WorkVolumeMaterial::whereIn('work_volume_materials.id', [$new_work_angle->id])
                ->with('manual.parameters')
                ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
                ->select('work_volume_materials.*', 'manual_material_categories.name', 'manual_material_categories.category_unit', 'manual_materials.category_id')
                ->get();

            $manual_works = ManualWork::whereIn('id', [4, 6, 9, 10, 15, 16, 19, 20, 27, 28])->get();

            $count_work_1 = [];

            foreach ($manual_works as $work) {
                $count_work_1[$work->id.$work->name] = 0;
            }

            $count_work_2 = $count_work_1;

            foreach ($manual_material as $material) {
                foreach ($manual_works as $work) {
                    if ($material->category_unit == $work->unit) {
                        $count_work_1[$work->id.$work->name] += $material->count * $new_angle_unit_value->value;
                    }
                }
            }

            foreach ($manual_material as $material) {
                foreach ($material->parameters as $parameter) {
                    foreach ($manual_works as $work) {
                        if ($parameter->unit == $work->unit) {
                            $count_work_2[$work->id.$work->name] += $material->count;
                        }
                    }
                }
            }

            foreach ($count_work_1 as $key => $value) {
                if ($value == 0) {
                    unset($count_work_1[$key]);
                }
            }

            $work_counts_angle = array_values(array_merge($count_work_2, $count_work_1));
        }

        $new_works = [];
        $used_works = [];
        foreach ($manual_works as $key => $work) {
            if ($work->id == 10 || $work->id == 16) {
                if (! $request->is_out) {
                    continue;
                }
            }
            if ($request->dive_type == 'static') {
                if ($work->id == 16 or $work->id == 15) {
                    continue;
                }
            } else {
                if ($work->id == 20 or $work->id == 19) {
                    continue;
                }
            }

            if ($work->id == 28 or $work->id == 27) {
                if ($length_tongue == 12) {
                    continue;
                } elseif ($length_tongue > 12) {
                    if ($work->id == 28) {
                        continue;
                    }
                }
            }
            if ($work->id == 9 or $work->id == 19 or ($work->id == 20 and $request->is_out) or ($work->id == 10 and $request->is_out)) {
                if ($request->count_angle and $request->type_angle) {
                    if ($work->id == 19 or $work->id == 20) {
                        $term = 0;
                    } else {
                        $term = $work_counts_angle[$key] / $work->unit_per_days;
                    }
                    $new_works[] = [
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_work_id' => $work->id,
                        'count' => $work_counts_angle[$key],
                        'term' => ceil($term),
                        'is_tongue' => 1,
                        'price_per_one' => $work->price_per_unit,
                        'result_price' => $work_counts_angle[$key] * $work->price_per_unit,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                $used_works[] = $work->id;
            }
            if ($work->id == 4 or $work->id == 6 or $work->id == 9 or $work->id == 10 or $work->id == 16 or ($work->id == 20 and $request->is_out) or $work->id == 15 or $work->id == 19 or $work->id == 28 or $work->id == 27) {
                if ($work->id == 15 or $work->id == 16) {
                    $term = $tongue_count / 15;
                } elseif ($work->id == 19 or $work->id == 20) {
                    $term = $tongue_count / 8;
                } elseif ($work->id == 27 or $work->id == 28) {
                    $term = $tongue_count / 8;
                } else {
                    $term = $work_counts_tongue[$key] / $work->unit_per_days;
                }
                $new_works[] = [
                    'user_id' => Auth::user()->id,
                    'work_volume_id' => $work_volume_id,
                    'manual_work_id' => $work->id,
                    'count' => $work_counts_tongue[$key],
                    'term' => $term,
                    'is_tongue' => 1,
                    'price_per_one' => $work->price_per_unit,
                    'result_price' => $work_counts_tongue[$key] * $work->price_per_unit,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                $used_works[] = $work->id;
            }
        }
        $used_works = array_unique($used_works);

        foreach ($new_works as $item) {
            $find_work = WorkVolumeWork::where('work_volume_id', $work_volume_id)
                ->where('manual_work_id', $item['manual_work_id'])
                ->first();

            if ($find_work) {
                $new_work = $find_work;
            } else {
                $new_work = new WorkVolumeWork();
            }

            $new_work->user_id = Auth::user()->id;
            $new_work->work_volume_id = $work_volume_id;
            $new_work->manual_work_id = $item['manual_work_id'];
            if ($item['manual_work_id'] == 4 or $item['manual_work_id'] == 6) {
                $new_work->count = 1;
                $new_work->term = 1;
            } else {
                $new_work->count += in_array($item['manual_work_id'], $worksWithCount) ? round($item['count'], 0) : $item['count'];
                $new_work->term += ceil($item['term']);

            }
            $new_work->is_tongue = 1;
            $new_work->order = WorkVolumeWork::where('work_volume_id', $work_volume_id)->max('order') + 1;

            // TODO make nice query
            $new_work->price_per_one = $item['price_per_one'];
            $new_work->result_price = $new_work->price_per_one * $new_work->count;

            $new_work->save();

            if (in_array($new_work->manual_work_id, $used_works)) {
                if ($request->count_angle and $request->type_angle) {

                    if ($item['manual_work_id'] == 9 or $item['manual_work_id'] == 19 or ($item['manual_work_id'] == 20 and $request->is_out) or ($item['manual_work_id'] == 10 and $request->is_out)) {
                        $new_material = new WorkVolumeWorkMaterial();

                        $new_material->wv_work_id = $new_work->id;
                        $new_material->wv_material_id = $new_work_angle->id;

                        $new_material->save();

                    }
                }

                if ($item['manual_work_id'] == 4 or $item['manual_work_id'] == 6) {

                } else {
                    $new_material = new WorkVolumeWorkMaterial();

                    $new_material->wv_work_id = $new_work->id;
                    $new_material->wv_material_id = $new_work_tongue->id;

                    $new_material->save();
                }
            }

            foreach (array_keys($used_works, $new_work->manual_work_id, true) as $key) {
                unset($used_works[$key]);
            }
        }

        DB::commit();

        return redirect()->back();
    }

    public function get_tongue(Request $request)
    {
        $wv_materials = ManualMaterial::where('manual_materials.category_id', 2);

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        $wv_materials = $wv_materials
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.category_unit')
            ->get();

        $results = [];
        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name.', '.$wv_material->category_unit,
                'unit' => $wv_material->category_unit,
            ];
        }

        return ['results' => $results];
    }

    public function get_angle(Request $request)
    {
        $wv_materials = ManualMaterial::where('manual_materials.category_id', 10);

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        $wv_materials = $wv_materials
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.category_unit')
            ->get();

        $results = [];

        $results[] = ['id' => 0, 'text' => 'Не выбрано', 'unit' => ''];

        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
                'unit' => $wv_material->category_unit,
            ];
        }

        return ['results' => $results];
    }

    public function calc_tongue_count(Request $request)
    {

        $parameters = ManualMaterialParameter::where('mat_id', $request->material_id)
            ->where('manual_material_category_attributes.name', 'Ширина профиля по центрам замков b')
            ->leftJoin('manual_material_category_attributes', 'manual_material_category_attributes.id', 'manual_material_parameters.attr_id')
            ->select('manual_material_parameters.*', 'manual_material_category_attributes.name')
            ->first();

        if (! $parameters) {
            return \GuzzleHttp\json_encode(0);
        }

        $result = $request->perimeter / ($parameters->value * 0.001);

        return \GuzzleHttp\json_encode(ceil($result));
    }

    public function get_pipe(Request $request)
    {
        $wv_materials = ManualMaterial::whereIn('manual_materials.category_id', [7, 8, 9]);

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        $wv_materials = $wv_materials
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.category_unit')
            ->get();

        $results = [];
        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
                'unit' => $wv_material->category_unit,
            ];
        }

        return ['results' => $results];
    }

    public function get_beam(Request $request)
    {
        $wv_materials = ManualMaterial::where('manual_materials.category_id', 4);

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        $wv_materials = $wv_materials
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.category_unit')
            ->get();

        $results = [];
        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
                'unit' => $wv_material->category_unit,
            ];
        }

        return ['results' => $results];
    }

    public function get_detail(Request $request)
    {
        $nodes = ManualNodes::where('node_category_id', 6);

        if ($request->q) {
            $nodes = $nodes->where('name', 'like', '%'.trim($request->q).'%');
        }

        $nodes = $nodes->get();

        $results = [];

        foreach ($nodes as $node) {
            $results[] = [
                'id' => $node->id,
                'text' => $node->name,
            ];
        }

        return ['results' => $results];
    }

    public function get_nodes(Request $request)
    {
        $nodes = ManualNodes::whereIn('node_category_id', [5, 7, 8]);

        if ($request->q) {
            $nodes = $nodes->where('name', 'like', '%'.trim($request->q).'%');
        }

        $nodes = $nodes->get();

        $results = [];

        foreach ($nodes as $node) {
            $results[] = [
                'id' => $node->id,
                'text' => $node->name,
                'unit' => 'шт',
                'select' => 'nodes[]',
                'input' => 'nodes_count[]',
            ];
        }

        $wv_materials = ManualMaterial::whereIn('manual_materials.category_id', [5]);

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        $wv_materials = $wv_materials
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.category_unit')
            ->get();

        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
                'unit' => $wv_material->category_unit,
                'category_id' => $wv_material->category_id,
                'select' => 'sheets[]',
                'input' => 'sheets_count[]',
            ];
        }

        return ['results' => $results];
    }

    public function calculate_mount($material, $type, $request, $work_volume_id)
    {
        $materials_ids = [];

        foreach ($material as $item) {
            $materials_ids[] = $item->id;
        }

        $manual_material = WorkVolumeMaterial::whereIn('work_volume_materials.id', $materials_ids)
            ->with('manual.parameters')
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('work_volume_materials.*', 'manual_material_categories.name', 'manual_material_categories.category_unit', 'manual_materials.category_id')
            ->get();

        $manual_works = ManualWork::whereIn('id', [43, 44, 47, 48, 49, 50])->get();

        $count_work_1 = [];

        foreach ($manual_works as $work) {
            $count_work_1[$work->id.$work->name] = 0;
        }

        $count_work_2 = $count_work_1;

        foreach ($manual_material as $material) {
            foreach ($manual_works as $work) {
                if ($material->category_unit == $work->unit) {
                    $count_work_1[$work->id.$work->name] += $material->count;
                }
            }
        }

        foreach ($manual_material as $material) {
            foreach ($material->parameters as $parameter) {
                foreach ($manual_works as $work) {
                    if ($parameter->unit == $work->unit) {
                        $count_work_2[$work->id.$work->name] += $material->count * $parameter->value;
                    }
                }
            }
        }

        foreach ($count_work_1 as $key => $value) {
            if ($value == 0) {
                unset($count_work_1[$key]);
            }
        }

        $work_counts = array_values(array_merge($count_work_2, $count_work_1));

        foreach ($manual_works as $key => $work) {
            if (! $request->is_out) {
                if ($work->id == 48 or $work->id == 50) {
                    continue;
                }
            }
            $new_works[] = [
                'user_id' => Auth::user()->id,
                'work_volume_id' => $work_volume_id,
                'manual_work_id' => $work->id,
                'count' => round($work_counts[$key], 3),
                'term' => ceil($work_counts[$key] / $work->unit_per_days),
                'is_tongue' => 1,
                'price_per_one' => $work->price_per_unit,
                'result_price' => $work_counts[$key] * $work->price_per_unit,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        $mat_count = 0;
        foreach ($new_works as $item) {
            $find_work = WorkVolumeWork::where('work_volume_id', $work_volume_id)
                ->where('manual_work_id', $item['manual_work_id'])
                ->first();

            if ($find_work) {
                $new_work = $find_work;
            } else {
                $new_work = new WorkVolumeWork();
            }

            $new_work->user_id = Auth::user()->id;
            $new_work->work_volume_id = $work_volume_id;
            $new_work->manual_work_id = $item['manual_work_id'];
            if ($item['manual_work_id'] == 43 or $item['manual_work_id'] == 44) {
                $new_work->count = 1;
                $new_work->term = 1;
            } else {
                if ($item['manual_work_id'] == 49) {
                    if ($type == 1) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->strapping_beam_count) / 8);
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    } elseif ($type == 2) {
                        foreach ($request->corner_strut_length as $key => $value) {
                            if ($value < 12) {
                                $new_work->term += ceil($request->corner_strut_count[$key] / 2);
                            } else {
                                $new_work->term += ceil($request->corner_strut_count[$key]);
                            }
                        }
                    } elseif ($type == 3) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->cross_strut_count));
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    } elseif ($type == 4) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->strut_count));
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    }
                } elseif ($item['manual_work_id'] == 50) {
                    if ($type == 1) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->strapping_beam_count) / 20);
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    } elseif ($type == 2) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->corner_strut_count) / 3);
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    } elseif ($type == 3) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->cross_strut_count) / 3);
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    } elseif ($type == 4) {
                        if ($mat_count == 0) {
                            $new_work->term += ceil(array_sum($request->strut_count) / 3);
                        } else {
                            $new_work->term = 0;
                            $mat_count += 1;
                        }
                    }
                } else {
                    $new_work->term += ceil($item['term']);
                }
                $new_work->count += $item['count'];
            }

            $new_work->is_tongue = 1;
            $new_work->order = WorkVolumeWork::where('work_volume_id', $work_volume_id)->max('order') + 1;

            // TODO make nice query
            $new_work->price_per_one = $item['price_per_one'];
            $new_work->result_price = $new_work->price_per_one * $new_work->count;

            $new_work->save();

            if ($item['manual_work_id'] == 47 or $item['manual_work_id'] == 48 or $item['manual_work_id'] == 49 or $item['manual_work_id'] == 50) {
                foreach ($materials_ids as $id) {
                    $new_material = new WorkVolumeWorkMaterial();

                    $new_material->wv_work_id = $new_work->id;
                    $new_material->wv_material_id = $id;

                    $new_material->save();
                }
            }
        }

    }

    public function create_mount_calc(Request $request, $work_volume_id): RedirectResponse
    {
        DB::beginTransaction();

        $work_volume = WorkVolume::where('id', $work_volume_id);

        $work_volume_works = WorkVolumeWork::where('work_volume_id', $work_volume_id)->pluck('manual_work_id')->toArray();

        $request->strapping_beam;
        $request->corner_strut;
        $request->cross_strut;
        $request->strut;
        // $request->embedded_parts;
        $request->racks;
        // $request->nodes;

        $materials = ManualMaterial::whereIn('id', array_merge($request->strapping_beam, $request->corner_strut, $request->cross_strut, $request->strut, $request->racks))
            ->with('parameters')
            ->get();

        $strapping_beam = []; // 1
        $corner_strut = []; // 2
        $cross_strut = []; // 3
        $strut = []; // 4
        $racks = [];
        $material_ids = [];
        $common_material = 0;

        if ($request->strapping_beam[0] != null) {
            foreach ($request->strapping_beam as $key => $item) {
                if ($item) {
                    $common_material = WorkVolumeMaterial::create([
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_material_id' => $item,
                        'is_our' => 1,
                        'count' => round($request->strapping_beam_count_weight[$key], 3),
                        'is_tongue' => 1,
                        'price_per_one' => $materials->where('id', $item)->first()->buy_cost,
                        'result_price' => $request->strapping_beam_count[$key] * $materials->where('id', $item)->first()->buy_cost,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    $strapping_beam[] = $common_material;
                    $material_ids[] = $common_material->id;
                }
            }

            $this->calculate_mount($strapping_beam, 1, $request, $work_volume_id);
        }

        if ($request->corner_strut[0] != null) {
            foreach ($request->corner_strut as $key => $item) {
                if ($item) {
                    $common_material = WorkVolumeMaterial::create([
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_material_id' => $item,
                        'is_our' => 1,
                        'count' => round($request->corner_strut_count_weight[$key], 3),
                        'is_tongue' => 1,
                        'price_per_one' => $materials->where('id', $item)->first()->buy_cost,
                        'result_price' => $request->corner_strut_length[$key] * $materials->where('id', $item)->first()->buy_cost,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $corner_strut[] = $common_material;
                    $material_ids[] = $common_material->id;
                }
            }

            $this->calculate_mount($corner_strut, 2, $request, $work_volume_id);
        }

        if ($request->cross_strut[0] != null) {
            foreach ($request->cross_strut as $key => $item) {
                if ($item) {
                    $common_material = WorkVolumeMaterial::create([
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_material_id' => $item,
                        'is_our' => 1,
                        'count' => round($request->cross_strut_count_weight[$key], 3),
                        'is_tongue' => 1,
                        'price_per_one' => $materials->where('id', $item)->first()->buy_cost,
                        'result_price' => $request->cross_strut_count[$key] * $materials->where('id', $item)->first()->buy_cost,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $cross_strut[] = $common_material;
                    $material_ids[] = $common_material->id;
                }
            }

            $this->calculate_mount($cross_strut, 3, $request, $work_volume_id);
        }

        if ($request->strut[0] != null) {
            foreach ($request->strut as $key => $item) {
                if ($item) {
                    $common_material = WorkVolumeMaterial::create([
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_material_id' => $item,
                        'is_our' => 1,
                        'count' => round($request->strut_count_weight[$key], 3),
                        'is_tongue' => 1,
                        'price_per_one' => $materials->where('id', $item)->first()->buy_cost,
                        'result_price' => $request->strut_count[$key] * $materials->where('id', $item)->first()->buy_cost,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $strut[] = $common_material;
                    $material_ids[] = $common_material->id;
                }
            }

            $this->calculate_mount($strut, 4, $request, $work_volume_id);
        }

        $racks_ids = [];

        if ($request->racks[0] != null and $request->racks_count[0] != null and $request->racks_length[0] != null) {
            foreach ($request->racks as $key => $item) {
                if ($item) {
                    $common_material = WorkVolumeMaterial::create([
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_material_id' => $item,
                        'is_our' => 1,
                        'count' => round($request->racks_count_weight[$key], 3),
                        'is_tongue' => 1,
                        'price_per_one' => $materials->where('id', $item)->first()->buy_cost,
                        'result_price' => $request->racks_count[$key] * $materials->where('id', $item)->first()->buy_cost,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    $racks_ids[] = $common_material->id;
                    $racks[] = $common_material;
                    $material_ids[] = $common_material->id;
                }
            }
            $manual_material = WorkVolumeMaterial::whereIn('work_volume_materials.id', $racks_ids)
                ->with('manual.parameters')
                ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
                ->select('work_volume_materials.*', 'manual_material_categories.name', 'manual_material_categories.category_unit', 'manual_materials.category_id')
                ->get();

            $manual_works = ManualWork::whereIn('id', [51, 52, 47, 48])->get();

            $count_work_1 = [];

            foreach ($manual_works as $work) {
                $count_work_1[$work->id.$work->name] = 0;
            }

            $count_work_2 = $count_work_1;

            foreach ($manual_material as $material) {
                foreach ($manual_works as $work) {
                    if ($material->category_unit == $work->unit) {
                        $count_work_1[$work->id.$work->name] += $material->count;
                    }
                }
            }

            foreach ($manual_material as $material) {
                foreach ($material->parameters as $parameter) {
                    foreach ($manual_works as $work) {
                        if ($parameter->unit == $work->unit) {
                            $count_work_2[$work->id.$work->name] += $material->count * $parameter->value;
                        }
                    }
                }
            }

            foreach ($count_work_1 as $key => $value) {
                if ($value == 0) {
                    unset($count_work_1[$key]);
                }
            }

            $racks_count = array_values(array_merge($count_work_2, $count_work_1));

            foreach ($manual_works as $key => $work) {
                if ($request->is_out) {
                    if ($work->id == 48 or $work->id == 52) {
                        continue;
                    }
                }

                $new_works_racks[] = [
                    'user_id' => Auth::user()->id,
                    'work_volume_id' => $work_volume_id,
                    'manual_work_id' => $work->id,
                    'count' => round($racks_count[$key], 3),
                    'term' => ceil($racks_count[$key] / $work->unit_per_days),
                    'is_tongue' => 1,
                    'price_per_one' => $work->price_per_unit,
                    'result_price' => $racks_count[$key] * $work->price_per_unit,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            $racks_mat_count = 0;
            foreach ($new_works_racks as $key_req => $item) {
                $find_work = WorkVolumeWork::where('work_volume_id', $work_volume_id)
                    ->where('manual_work_id', $item['manual_work_id'])
                    ->first();

                if ($find_work) {
                    $new_work = $find_work;
                } else {
                    $new_work = new WorkVolumeWork();
                }

                $new_work->user_id = Auth::user()->id;
                $new_work->work_volume_id = $work_volume_id;
                $new_work->manual_work_id = $item['manual_work_id'];
                $new_work->count += $item['count'];
                if ($item['manual_work_id'] == 51 or $item['manual_work_id'] == 52) {
                    if ($racks_mat_count == 0) {
                        $new_work->term += ceil(array_sum($request->racks_count) / 8);
                    } else {
                        $new_work->term = 0;
                        $racks_mat_count += 1;
                    }
                } else {
                    $new_work->term += ceil($item['term']);
                }
                $new_work->is_tongue = 1;
                $new_work->order = WorkVolumeWork::where('work_volume_id', $work_volume_id)->max('order') + 1;

                // TODO make nice query
                $new_work->price_per_one = $item['price_per_one'];
                $new_work->result_price = $new_work->price_per_one * $new_work->count;

                $new_work->save();

                foreach ($racks as $item) {
                    $new_material = new WorkVolumeWorkMaterial();

                    $new_material->wv_work_id = $new_work->id;
                    $new_material->wv_material_id = $item->id;

                    $new_material->save();
                }

            }
        }

        $material_id_embedds = [];
        if ($request->embedded_parts[0] != null and $request->embedded_parts_count[0] != null) {
            foreach ($request->embedded_parts as $key_main => $item) {
                $manual_materials_id = ManualNodeMaterials::where('node_id', $item)->pluck('manual_material_id')->toArray();
                $manual_materials_value = ManualNodeMaterials::where('node_id', $item)->pluck('count')->toArray();

                $materials = ManualMaterial::whereIn('id', $manual_materials_id)
                    ->with('parameters')
                    ->get();

                foreach ($manual_materials_id as $key => $id) {
                    $emb_id = WorkVolumeMaterial::create([
                        'user_id' => Auth::user()->id,
                        'work_volume_id' => $work_volume_id,
                        'manual_material_id' => $id,
                        'is_our' => 1,
                        'count' => round($manual_materials_value[$key] * $request->embedded_parts_count[$key_main], 2) * 1.05,
                        'is_tongue' => 1,
                        'price_per_one' => isset($materials->where('id', $id)->first()->buy_cost) ? $materials->where('id', $id)->first()->buy_cost : 0,
                        'result_price' => $manual_materials_value[$key] * $request->embedded_parts_count[$key_main] * isset($materials->where('id', $id)->first()->buy_cost) ? $materials->where('id', $id)->first()->buy_cost : 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $material_id_embedds[] = $emb_id->id;
                }
            }

            $manual_material = WorkVolumeMaterial::whereIn('work_volume_materials.id', $material_id_embedds)
                ->with('manual.parameters')
                ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
                ->select('work_volume_materials.*', 'manual_material_categories.name', 'manual_material_categories.category_unit', 'manual_materials.category_id', 'manual_materials.id as manual_id')
                ->get();

            $manual_works = ManualWork::whereIn('id', [43, 44, 47, 48, 50, 66])->get();

            $count_work_1 = [];

            $material_exist_1 = [];
            $material_exist_2 = [];

            foreach ($manual_works as $work) {
                $count_work_1[$work->id.$work->name] = 0;
            }

            $count_work_2 = $count_work_1;

            foreach ($manual_material as $material) {
                foreach ($manual_works as $work) {
                    if ($material->category_unit == $work->unit) {
                        $count_work_1[$work->id.$work->name] += (float) number_format($material->count, 3);
                        $material_exist_1[] = $material->manual_id;
                    }
                }
            }

            foreach ($manual_material as $material) {
                foreach ($material->parameters as $parameter) {
                    foreach ($manual_works as $work) {
                        if ($parameter->unit == $work->unit) {
                            $count_work_2[$work->id.$work->name] += (float) number_format($material->count * $parameter->value, 3);
                            $material_exist_2[] = $material->manual_id;
                        }
                    }
                }
            }
            foreach ($count_work_1 as $key => $value) {
                if ($value == 0) {
                    unset($count_work_1[$key]);
                }
            }

            $embedded_parts_count = array_values(array_merge($count_work_2, $count_work_1));

            //            if(!(array_unique($material_exist_1) == array_unique($material_exist_2))) {
            //                $count_work_2 = array_values($count_work_2);
            //                foreach ($embedded_parts_count as $key => $value) {
            //                    $embedded_parts_count[$key] = (float) number_format($count_work_2[$key], 3);
            //                }
            //            }

            foreach ($manual_works as $key => $work) {
                $new_works_embedded_parts[] = [
                    'user_id' => Auth::user()->id,
                    'work_volume_id' => $work_volume_id,
                    'manual_work_id' => $work->id,
                    'count' => round($embedded_parts_count[$key], 3),
                    'term' => ceil($embedded_parts_count[$key] / $work->unit_per_days),
                    'is_tongue' => 1,
                    'price_per_one' => $work->price_per_unit,
                    'result_price' => $embedded_parts_count[$key] * $work->price_per_unit,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            foreach ($new_works_embedded_parts as $item) {
                $find_work = WorkVolumeWork::where('work_volume_id', $work_volume_id)
                    ->where('manual_work_id', $item['manual_work_id'])
                    ->first();

                if ($find_work) {
                    $new_work = $find_work;
                } else {
                    $new_work = new WorkVolumeWork();
                }

                $new_work->user_id = Auth::user()->id;
                $new_work->work_volume_id = $work_volume_id;
                $new_work->manual_work_id = $item['manual_work_id'];
                if ($item['manual_work_id'] == 43 or $item['manual_work_id'] == 44) {
                    $new_work->count = 1;
                    $new_work->term = 1;
                } elseif ($item['manual_work_id'] == 66 or $item['manual_work_id'] == 50) {
                    $new_work->count += $item['count'];
                    $new_work->term += 0;
                } else {
                    $new_work->count += $item['count'];
                    $new_work->term += ceil($item['term']);
                }
                $new_work->is_tongue = 1;
                $new_work->order = WorkVolumeWork::where('work_volume_id', $work_volume_id)->max('order') + 1;

                // TODO make nice query
                $new_work->price_per_one = $item['price_per_one'];
                $new_work->result_price = $new_work->price_per_one * $new_work->count;

                $new_work->save();

                if (! ($item['manual_work_id'] == 43 or $item['manual_work_id'] == 44)) {
                    foreach ($material_id_embedds as $id) {
                        $new_material = new WorkVolumeWorkMaterial();

                        $new_material->wv_work_id = $new_work->id;
                        $new_material->wv_material_id = $id;

                        $new_material->save();
                    }
                }
            }
        }

        $matedial_nodes_id = [];
        if (($request->nodes_count[0] and $request->nodes[0]) or ($request->sheets_count[0] and $request->sheets[0])) {
            if (($request->nodes_count[0] and $request->nodes[0])) {
                foreach ($request->nodes as $key_main => $item) {
                    if ($item) {
                        $manual_materials_id = ManualNodeMaterials::where('node_id', $item)->pluck('manual_material_id')->toArray();
                        $manual_materials_value = ManualNodeMaterials::where('node_id', $item)->pluck('count')->toArray();
                        $node = ManualNodes::with('node_materials')->findOrFail($item);
                        $materials = ManualMaterial::whereIn('id', [$manual_materials_id])
                            ->with('parameters')
                            ->get();

                        foreach ($manual_materials_id as $key => $id) {
                            $node_mat = WorkVolumeMaterial::create([
                                'user_id' => Auth::user()->id,
                                'work_volume_id' => $work_volume_id,
                                'manual_material_id' => $id,
                                'is_our' => 1,
                                'count' => round($manual_materials_value[$key] * $request->nodes_count[$key_main], 3) * (1 + $node->node_category->safety_factor / 100),
                                'is_tongue' => 1,
                                'price_per_one' => isset($materials->where('id', $id)->first()->buy_cost) ? $materials->where('id', $id)->first()->buy_cost : 0,
                                'result_price' => $manual_materials_value[$key] * $request->nodes_count[$key_main] * isset($materials->where('id', $id)->first()->buy_cost) ? $materials->where('id', $id)->first()->buy_cost : 0,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                            $matedial_nodes_id[] = $node_mat->id;
                        }
                    }

                }
            }

            if ($request->sheets[0] and $request->sheets_count[0]) {
                $materials = ManualMaterial::whereIn('id', $request->sheets)
                    ->with('parameters')
                    ->get();

                foreach ($request->sheets as $key => $id) {
                    if ($id) {
                        $sheets_mat = WorkVolumeMaterial::create([
                            'user_id' => Auth::user()->id,
                            'work_volume_id' => $work_volume_id,
                            'manual_material_id' => $id,
                            'is_our' => 1,
                            'count' => round($request->sheets_count[$key], 3),
                            'is_tongue' => 1,
                            'price_per_one' => isset($materials->where('id', $id)->first()->buy_cost) ? $materials->where('id', $id)->first()->buy_cost : 0,
                            'result_price' => $request->sheets_count[$key] * isset($materials->where('id', $id)->first()->buy_cost) ? $materials->where('id', $id)->first()->buy_cost : 0,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                        $matedial_nodes_id[] = $sheets_mat->id;

                    }
                }
            }

            $manual_material = WorkVolumeMaterial::whereIn('work_volume_materials.id', $matedial_nodes_id)
                ->where('is_node', 0)
                ->with('manual.parameters')
                ->leftJoin('manual_materials', 'manual_materials.id', '=', 'work_volume_materials.manual_material_id')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
                ->select('work_volume_materials.*', 'manual_material_categories.name', 'manual_material_categories.category_unit', 'manual_materials.category_id')
                ->get();

            $manual_works = ManualWork::whereIn('id', [43, 44, 47, 48, 49, 50])->get();

            $count_work_1 = [];

            foreach ($manual_works as $work) {
                $count_work_1[$work->id.$work->name] = 0;
            }

            $count_work_2 = $count_work_1;

            foreach ($manual_material as $material) {
                foreach ($manual_works as $work) {
                    if ($material->category_unit == $work->unit) {
                        $count_work_1[$work->id.$work->name] += $material->count;
                    }
                }
            }

            foreach ($manual_material as $material) {
                foreach ($material->parameters as $parameter) {
                    foreach ($manual_works as $work) {
                        if ($parameter->unit == $work->unit) {
                            $count_work_2[$work->id.$work->name] += $material->count * $parameter->value;
                        }
                    }
                }
            }

            foreach ($count_work_1 as $key => $value) {
                if ($value == 0) {
                    unset($count_work_1[$key]);
                }
            }
            $nodes_count = array_values(array_merge($count_work_2, $count_work_1));
            foreach ($manual_works as $key => $work) {

                if ($work->id == 48 or $work->id == 50) {
                    if (! $request->is_out) {
                        continue;
                    }
                }

                $new_work_nodes[] = [
                    'user_id' => Auth::user()->id,
                    'work_volume_id' => $work_volume_id,
                    'manual_work_id' => $work->id,
                    'count' => round($nodes_count[$key], 3),
                    'term' => ceil(($nodes_count[$key]) / $work->unit_per_days),
                    'is_tongue' => 1,
                    'price_per_one' => $work->price_per_unit,
                    'result_price' => $nodes_count[$key] * $work->price_per_unit,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            foreach ($new_work_nodes as $item) {
                $find_work = WorkVolumeWork::where('work_volume_id', $work_volume_id)
                    ->where('manual_work_id', $item['manual_work_id'])
                    ->first();

                if ($find_work) {
                    $new_work = $find_work;
                } else {
                    $new_work = new WorkVolumeWork();
                }

                $new_work->user_id = Auth::user()->id;
                $new_work->work_volume_id = $work_volume_id;
                $new_work->manual_work_id = $item['manual_work_id'];
                if ($item['manual_work_id'] == 43 or $item['manual_work_id'] == 44) {
                    $new_work->count = 1;
                    $new_work->term = 1;
                } elseif ($item['manual_work_id'] == 49 or $item['manual_work_id'] == 50) {
                    $new_work->count += $item['count'];
                    $new_work->term += 0;
                } else {
                    $new_work->count += $item['count'];
                    $new_work->term += ceil($item['term']);
                }

                $new_work->is_tongue = 1;
                $new_work->order = WorkVolumeWork::where('work_volume_id', $work_volume_id)->max('order') + 1;

                // TODO make nice query
                $new_work->price_per_one = $item['price_per_one'];
                $new_work->result_price = $new_work->price_per_one * $new_work->count;

                $new_work->save();

                if (! ($item['manual_work_id'] == 43 or $item['manual_work_id'] == 44)) {
                    foreach ($matedial_nodes_id as $id) {
                        $new_material = new WorkVolumeWorkMaterial();

                        $new_material->wv_work_id = $new_work->id;
                        $new_material->wv_material_id = $id;

                        $new_material->save();
                    }
                }
            }
        }

        DB::commit();

        return redirect()->back();
    }

    public function count_weight(Request $request): JsonResponse
    {
        $material_value = 0;

        if ($request->mat_id) {
            $material_value = ManualMaterial::where('id', $request->mat_id)->first()->parameters()->orderBy('id', 'desc')->where('unit', 'кг')->first()->value ?? 0;
        }

        return response()->json(['material_value' => ($material_value / 1000) ?? 0]);
    }
}
