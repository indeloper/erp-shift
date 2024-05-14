<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest\WorkVolumeMaterialRequest;
use App\Http\Requests\ProjectRequest\WorkVolumeReqRequest;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\CommercialOffer\CommercialOfferRequest;
use App\Models\CommercialOffer\CommercialOfferWork;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\Manual\ManualNodeMaterials;
use App\Models\Manual\ManualNodes;
use App\Models\Manual\ManualWork;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeMaterialComplect;
use App\Models\WorkVolume\WorkVolumeRequest;
use App\Models\WorkVolume\WorkVolumeRequestFile;
use App\Models\WorkVolume\WorkVolumeWork;
use App\Models\WorkVolume\WorkVolumeWorkMaterial;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProjectWorkVolumeController extends Controller
{
    use TimeCalculator;

    public function change_depth(Request $request, $work_volume_id)
    {
        $work_volume = WorkVolume::findOrFail($work_volume_id);
        $work_volume->depth = $request->depth;
        $work_volume->save();
    }

    public function card_tongue($project_id, $work_volume_id)
    {
        $work_volume = WorkVolume::where('work_volumes.id', $work_volume_id)
            ->with('works_tongue.materials', 'works_tongue.manual', 'shown_materials', 'shown_materials.parts')
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name')
            ->first();

        if ($work_volume->type != 0) {
            abort(403);
        }

        $work_volume_requests = WorkVolumeRequest::where('work_volume_id', $work_volume->id)
            ->where('tongue_pile', 0)
            ->leftJoin('users', 'users.id', '=', 'work_volume_requests.user_id')
            ->select('work_volume_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $request_tasks = Task::where('project_id', $project_id)
            ->whereIn('status', ['14', '15', '16', '17'])
            ->where('is_solved', 0)
            ->get();

        $complects = $work_volume->shown_materials->where('material_type', 'complect');

        $work_volume_materials_card = WorkVolumeMaterial::where('work_volume_id', $work_volume_id)
            ->where('work_volume_materials.is_tongue', 1)
            ->where('material_type', 'regular')
            ->where('complect_id', null)
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->groupBy('manual_material_id')
            ->select('work_volume_materials.*', 'manual_material_categories.category_unit', 'manual_materials.name',
                DB::raw('sum(count) as count'));

        $work_volume_materials = $work_volume->shown_materials;

        $resp = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 4)->first();

        $WV_resp = isset($resp) ? $resp->user_id : 0;

        $sop = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 2)->first();

        $categories = ManualMaterialCategory::whereNotIn('id', [12, 14])->with('attributes')->select('id', 'name')->get();

        return view('projects.work_volume.new_card', [
            'service_name' => 'Шпунтовое направление',
            'categories' => $categories,
            'work_volume' => $work_volume,
            'works' => $work_volume->works_tongue->sortBy('manual.work_group_id'),
            'work_volume_requests' => $work_volume_requests->get(),
            'request_tasks' => $request_tasks,
            'work_volume_materials' => $work_volume_materials,
            'work_volume_materials_card' => $work_volume_materials_card->get(),
            'is_tongue' => 1,
            'sop' => $sop,
            'WV_resp' => $WV_resp,
            'complects' => $complects,
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function card_pile($project_id, $work_volume_id)
    {
        $work_volume = WorkVolume::where('work_volumes.id', $work_volume_id)
            ->with('works_pile.materials', 'works_pile.manual')
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name')
            ->first();

        if ($work_volume->type != 1) {
            abort(403);
        }

        $work_volume_requests = WorkVolumeRequest::where('work_volume_id', $work_volume->id)
            ->where('tongue_pile', 1)
            ->leftJoin('users', 'users.id', '=', 'work_volume_requests.user_id')
            ->select('work_volume_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $request_tasks = Task::where('project_id', $project_id)->whereBetween('status', ['14', '15', '16', '17'])->where('is_solved', 0)->get();

        $work_volume_materials = WorkVolumeMaterial::where('work_volume_id', $work_volume_id)
            ->where('work_volume_materials.is_tongue', 0)
            ->leftJoin('manual_materials', 'manual_materials.id', '=', 'manual_material_id')
            ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('work_volume_materials.*', 'manual_material_categories.category_unit', 'manual_materials.name');

        $resp = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 3)->first();

        $WV_resp = isset($resp) ? $resp->user_id : 0;

        $categories = ManualMaterialCategory::where('id', 12)->with('attributes')->select('id', 'name')->get();

        return view('projects.work_volume.new_card', [
            'service_name' => 'Свайное направление',
            'work_volume' => $work_volume,
            'works' => $work_volume->works_pile->sortBy('manual.work_group_id'),
            'work_volume_requests' => $work_volume_requests->get(),
            'request_tasks' => $request_tasks,
            'work_volume_materials' => $work_volume_materials->get(),
            'work_volume_materials_card' => $work_volume_materials->get(),
            'is_tongue' => 0,
            'categories' => $categories,
            'sop' => 'default',
            'WV_resp' => $WV_resp,
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function delete_work(Request $request)
    {
        $work = WorkVolumeWork::find($request->wv_work_id);
        $work->materials()->detach();
        $work->delete();

        return \GuzzleHttp\json_encode(true);
    }

    public function get_one_work(Request $request)
    {
        $item = WorkVolumeWork::where('id', $request->wv_work_id)
            ->with('manual')
            ->first();

        return \GuzzleHttp\json_encode($item);
    }

    public function get_pile_name(Request $request)
    {
        $item = WorkVolumeMaterial::where('combine_id', $request->combine_id)->first();

        return \GuzzleHttp\json_encode($item->combine_pile());
    }

    public function get_one_work_manual(Request $request)
    {
        $item = ManualWork::where('id', $request->wv_work_id)
            ->first();

        return \GuzzleHttp\json_encode($item);
    }

    public function send_work_volume(Request $request, $work_volume_id)
    {
        if (! WorkVolumeWork::where('work_volume_id', $work_volume_id)->first()) {
            return back()->with('wv', 'Заполните объём работ');
        }

        DB::beginTransaction();

        $work_volume = WorkVolume::findOrFail($work_volume_id);

        $project = Project::findOrFail($work_volume->project_id);

        if ($request->is_tongue == 1) {
            $work_volume->is_save_tongue = 1;
            $task = Task::where('project_id', $project->id)->where('target_id', $work_volume_id)->where('status', 3)->where('is_solved', 0)->first();
        } elseif ($request->is_tongue == 0) { //close task расчёт объемов (сваи)
            $work_volume->is_save_pile = 1;
            $task = Task::where('project_id', $project->id)->where('status', 4)->where('is_solved', 0)->first();
        }

        if ($task) {
            Notification::create([
                'name' => 'Задача «'.$task->name.'» закрыта',
                'task_id' => $task->id,
                'user_id' => $task->responsible_user_id,
                'contractor_id' => $task->project_id ? $project->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? $project->object_id : null,
                'type' => 3,
            ]);

            $task->solve();
        }

        // block for task 18 solve
        $tasks_18 = Task::where('project_id', $project->id)->where('target_id', $work_volume_id)->where('status', 18)->where('is_solved', 0)->with('project.object')->get();
        if ($request->has('from_task_18') or $tasks_18->isNotEmpty()) {
            if ($tasks_18->isNotEmpty()) {
                foreach ($tasks_18 as $item) {
                    $item->result = 1;
                    $item->final_note = $item->descriptions[$item->status].$item->results[$item->status][$item->result];
                    $item->prev_task_id = $task->id ?? null;
                    $item->description = $request->final_note;
                    $item->solve_n_notify();
                }
            }
        }

        $work_volume->status = 2;

        $requests = WorkVolumeRequest::whereIn('work_volume_id', [0, $work_volume_id])
            ->where('project_id', $work_volume->project_id)
            ->where('status', 0)
            ->update(['status' => 2]);

        $offers_count = CommercialOffer::where('project_id', $work_volume->project_id)->whereOption($work_volume->option)->where('is_tongue', $request->is_tongue)->update(['status' => 3]);

        $offers_id = CommercialOffer::where('project_id', $work_volume->project_id)->whereOption($work_volume->option)->where('is_tongue', $request->is_tongue)->pluck('id')->toArray();

        $tasks = Task::where('project_id', $work_volume->project_id)->whereIn('status', [5, 6, 12, 15, 16])->whereIn('target_id', $offers_id)->where('is_solved', 0)->get();

        foreach ($tasks as $item) {     //close all tasks from last com_offer
            Notification::create([
                'name' => 'Задача «'.$item->name.'» закрыта',
                'task_id' => $item->id,
                'user_id' => $item->responsible_user_id,
                'contractor_id' => $item->project_id ? $project->contractor_id : null,
                'project_id' => $item->project_id ? $project : null,
                'object_id' => $item->project_id ? $project->object_id : null,
                'type' => 3,
            ]);

            $item->solve();
        }

        if ($request->has('noSOP')) {
            $thisTask = Task::where('project_id', $work_volume->project_id)->where('status', 15)->where('is_solved', 0)->count();

            if ($thisTask == 0) {
                $tongueTask = new Task();

                $tongueTask->project_id = $work_volume->project_id;
                $tongueTask->name = 'Назначение ответственного за КП (шпунт)';
                $tongueTask->status = 15;
                $tongueTask->responsible_user_id = Group::find(50/*7*/)->getUsers()->first()->id;
                $tongueTask->contractor_id = $project->contractor_id;
                $tongueTask->expired_at = Carbon::now()->addHours(3);
                $tongueTask->target_id = $work_volume_id;
                $tongueTask->prev_task_id = $task->id ?? null;

                $tongueTask->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: '.$tongueTask->task_route();
                $notification->update([
                    'name' => 'Новая задача «'.$tongueTask->name.'»',
                    'task_id' => $tongueTask->id,
                    'user_id' => $tongueTask->responsible_user_id,
                    'contractor_id' => $tongueTask->project_id ? $project->contractor_id : null,
                    'project_id' => $tongueTask->project_id ? $tongueTask->project_id : null,
                    'object_id' => $tongueTask->project_id ? $project->object_id : null,
                    'type' => 30,
                ]);
            }
        } else {
            $prev_com_offer = CommercialOffer::where('project_id', $work_volume->project_id)->whereOption($work_volume->option)->where('is_tongue', $request->is_tongue)->orderBy('version', 'desc')->first();

            $commercial_offer = new CommercialOffer();

            $commercial_offer->name = $request->is_tongue ? 'Коммерческое предложение (шпунтовое направление)' : 'Коммерческое предложение (свайное направление)';
            $commercial_offer->user_id = Auth::id();
            $commercial_offer->project_id = $work_volume->project_id;
            $commercial_offer->work_volume_id = $work_volume->id;
            $commercial_offer->option = $work_volume->option;
            $commercial_offer->status = 1;
            $commercial_offer->version = $offers_count + 1;
            $commercial_offer->file_name = 0;
            $commercial_offer->is_tongue = $request->is_tongue;

            $commercial_offer->save();

            $new_wv_mats = $work_volume->shown_materials()->groupBy(['material_type', 'manual_material_id', 'unit'])->select('*', DB::raw('sum(count) as count'))->get();

            if ($prev_com_offer) {
                foreach ($work_volume->works as $work) {
                    $work->result_price = $work->count * $work->price_per_one;
                    $work->save();
                }

                foreach ($prev_com_offer->notes as $item) {
                    $new_note = $item->replicate();
                    $new_note->commercial_offer_id = $commercial_offer->id;
                    $new_note->save();
                }

                foreach ($prev_com_offer->requirements as $item) {
                    $new_note = $item->replicate();
                    $new_note->commercial_offer_id = $commercial_offer->id;
                    $new_note->save();
                }

                foreach ($prev_com_offer->advancements as $item) {
                    $new_note = $item->replicate();
                    $new_note->commercial_offer_id = $commercial_offer->id;
                    $new_note->save();
                }

                //take new materials
                // $split_adapter = $new_wv_mats->groupBy(['material_type', 'manual_material_id'])->map(function ($groups) {
                //     return $groups->map(function ($group) {
                //         return $group->sum('count');});
                // });

                //get splits from previous com_offer
                $control_count = $prev_com_offer->mat_splits->groupBy(['material_type', 'man_mat_id', 'unit']); //here are old ones
                //creating splits for new commercial_offer
                foreach ($new_wv_mats as $material) {
                    if ($material->count == (isset($control_count[$material->material_type][$material->manual_material_id]) ? $control_count[$material->material_type][$material->manual_material_id]->whereIn('type', [1, 3, 5])->sum('count') : -1)) { //if there was no changes amount of
                        foreach ($control_count[$material->material_type][$material->manual_material_id] as $old_split) {
                            $new_split = $old_split->replicate();
                            $new_split->man_mat_id = $old_split->man_mat_id; //do we really need this?
                            $new_split->com_offer_id = $commercial_offer->id;
                            $new_split->unit = $old_split->unit;
                            $new_split->save();
                        }
                    } else {
                        CommercialOfferMaterialSplit::create([
                            'man_mat_id' => $material->manual_material_id,
                            'count' => $material->count,
                            'type' => 1,
                            'com_offer_id' => $commercial_offer->id,
                            'material_type' => $material->material_type,
                            'unit' => $material->unit,
                        ]);
                    }
                }
            } else {
                foreach ($new_wv_mats as $material) {
                    CommercialOfferMaterialSplit::create([
                        'man_mat_id' => $material->manual_material_id,
                        'type' => 1,
                        'count' => $material->count,
                        'com_offer_id' => $commercial_offer->id,
                        'material_type' => $material->material_type,
                    ]);
                }
                foreach ($work_volume->works as $work) {
                    CommercialOfferWork::create([
                        'work_volume_work_id' => $work->id,
                        'commercial_offer_id' => $commercial_offer->id,
                        'count' => $work->count,
                        'term' => $work->term,
                        'price_per_one' => $work->price_per_one,
                        'result_price' => $work->count * $work->price_per_one,
                        'subcontractor_file_id' => $work->subcontractor_file_id,
                        'is_hidden' => $work->is_hidden,
                        'order' => $work->order,
                        'unit' => $work->unit,
                    ]);
                }
            }

            $WV_accept_task = Task::where('status', 18)->where('target_id', $work_volume->id)->orderBy('id', 'desc')->first();
            $task_CO = new Task([
                'project_id' => $work_volume->project_id,
                'name' => 'Формирование КП'.($request->is_tongue ? ' (шпунтовое направление)' : ' (свайное направление)'),
                'responsible_user_id' => ProjectResponsibleUser::where('project_id', $work_volume->project_id)->where('role', ($request->is_tongue ? 2 : 1))->first()->user_id,
                'contractor_id' => $project->contractor_id,
                'target_id' => $commercial_offer->id,
                'expired_at' => $this->addHours(24),
                'prev_task_id' => $WV_accept_task->id ?? null,
                'status' => 5,
            ]);

            $task_CO->save();

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = ' Ссылка на задачу: '.$task_CO->task_route();
            $notification->update([
                'name' => 'Новая задача «'.$task_CO->name.'»',
                'task_id' => $task_CO->id,
                'user_id' => $task_CO->responsible_user_id,
                'contractor_id' => $task_CO->project_id ? $project->contractor_id : null,
                'project_id' => $task_CO->project_id ? $task_CO->project_id : null,
                'object_id' => $task_CO->project_id ? $project->object_id : null,
                'created_at' => Carbon::now()->addSeconds(1),
                'type' => $request->is_tongue ? 28 : 29,
            ]);

            $com_offer_request = new CommercialOfferRequest();
            $com_offer_request->user_id = 0;
            $com_offer_request->project_id = $work_volume->project_id;
            $com_offer_request->commercial_offer_id = $commercial_offer->id;
            $com_offer_request->status = 0;
            $com_offer_request->description = 'Сформирован новый объём работ, проверьте актуальность цен';
            $com_offer_request->is_tongue = $commercial_offer->is_tongue;

            $com_offer_request->save();
        }

        $work_volume->save();

        $project_now = Project::find($work_volume->project_id);

        if ($project_now->status < 3 || $project_now->status == 5) { //move project status forward
            Project::where('id', $work_volume->project_id)->update(['status' => 3]);
        }

        DB::commit();

        if ($work_volume->type == 0) {
            return redirect()->route('projects::work_volume::card_tongue', [$work_volume->project_id, $work_volume->id]);
        } elseif ($work_volume->type == 1) {
            return redirect()->route('projects::work_volume::card_pile', [$work_volume->project_id, $work_volume->id]);
        }
    }

    public function edit_one(Request $request)
    {
        DB::beginTransaction();

        $work_volume = WorkVolume::findOrFail($request->wv_id);

        $complect_ids = $work_volume->shown_materials->whereIn('id', $request->material_id)->where('material_type', 'complect');
        $mat_ids = $work_volume->shown_materials->whereIn('id', $request->material_id)->where('material_type', 'regular');

        foreach ($complect_ids as $mat) {
            if ($mat->material_type == 'complect') {
                $mat_ids = $mat_ids->merge($mat->parts);
            }
        }

        if ($work_volume->type == 1) {
            if ($mat_ids) {
                $combine_piles = collect([]);
                if ($request->material_id) {
                    foreach ($request->material_id as $key => $value) {
                        if (strlen($value) > 10) {
                            ! $mat_ids->contains($value) ?: $mat_ids->forget($key);
                            $combine_piles = $combine_piles->merge(WorkVolumeMaterial::where('combine_id', $value)->get());
                        }
                    }
                }
                $mat_ids = $mat_ids->merge($combine_piles);
            }
        }

        foreach ($request->work_id as $key_work => $value) {
            if ($key_work == 0) {
                // first work (first on page) -> update it!
                $edited_work = WorkVolumeWork::find($request->wv_work_id);
                $edited_work->manual_work_id = $request->work_id[$key_work];
                $edited_work->materials()->detach();
                $edited_work->materials()->attach($mat_ids);
                $edited_work->count = $request->work_count[$key_work];
                $edited_work->term = $request->work_term[$key_work];
                $edited_work->save();
            } else {
                // second, etc. works -> create it!
                $new_work = new WorkVolumeWork();

                $new_work->user_id = Auth::id();
                $new_work->work_volume_id = $request->wv_id;
                $new_work->manual_work_id = $request->work_id[$key_work];
                $new_work->count += $request->work_count[$key_work];
                $new_work->term += $request->work_term[$key_work];
                $new_work->is_tongue = $request->is_tongue;

                $new_work->order = WorkVolumeWork::where('work_volume_id', $request->wv_id)->max('order') + 1;

                // TODO make nice query
                $new_work->price_per_one = ManualWork::findOrFail($new_work->manual_work_id)->price_per_unit;
                $new_work->result_price = $new_work->price_per_one * $new_work->count;

                $new_work->save();

                if ($request->material_id) {
                    $new_work->materials()->attach($mat_ids);
                }
            }
        }

        DB::commit();

        return redirect()->back();
    }

    public function save_one(Request $request, $work_volume_id)
    {
        DB::beginTransaction();
        $mat_ids = $request->material_id ? $request->material_id : [];

        $work_volume = WorkVolume::findOrFail($work_volume_id);

        if ($work_volume->type == 1) {
            if ($mat_ids) {
                $combine_piles = [];
                foreach ($mat_ids as $key => $value) {
                    if (strlen($value) > 10) {
                        unset($mat_ids[$key]);
                        $combine_piles = array_merge(WorkVolumeMaterial::where('work_volume_id', $work_volume_id)->where('combine_id', $value)->pluck('id')->toArray(), $combine_piles);
                    }
                }
                $mat_ids = array_merge($combine_piles, $mat_ids);
            }
        } else {
            $complect_material = $work_volume->shown_materials->whereIn('id', $mat_ids)->where('material_type', 'complect');
            $mat_ids = $work_volume->shown_materials->whereIn('id', $mat_ids)->where('material_type', 'regular')->pluck('id')->toArray();

            $mat_ids = array_merge($mat_ids, $complect_material->pluck('parts')->flatten()->pluck('id')->toArray());
        }

        foreach ($request->work_id as $key_work => $value) {
            $find_work = WorkVolumeWork::where('work_volume_id', $work_volume_id)
                ->where('manual_work_id', $request->work_id[$key_work])
                ->first();

            if ($find_work) {
                $new_work = $find_work;
            } else {
                $new_work = new WorkVolumeWork();
            }

            $new_work->user_id = Auth::user()->id;
            $new_work->work_volume_id = $work_volume_id;
            $new_work->manual_work_id = $request->work_id[$key_work];
            $new_work->count += $request->work_count[$key_work];
            $new_work->term += $request->work_term[$key_work];
            $new_work->is_tongue = $request->is_tongue;
            $new_work->order = WorkVolumeWork::where('work_volume_id', $work_volume_id)->max('order') + 1;

            // TODO make nice query
            $new_work->price_per_one = ManualWork::findOrFail($request->work_id[$key_work])->price_per_unit;
            $new_work->result_price = $new_work->price_per_one * $new_work->count;

            $new_work->save();
            $new_work->refresh();
            if ($request->material_id) {
                $new_work->materials()->attach($mat_ids);
                $new_work->push();
            }
        }
        DB::commit();

        return redirect()->back();
    }

    public function get_work(Request $request)
    {
        $mat_ids = $request->material_ids ? $request->material_ids : [];

        $work_volume = WorkVolume::find($request->work_volume_id);

        $combine_piles = [];
        $complect_mat_ids = [];

        foreach ($mat_ids as $key => $value) {
            if (strlen($value) > 8) {
                unset($mat_ids[$key]);
                $combine_piles = array_merge(WorkVolumeMaterial::where('combine_id', $value)->pluck('id')->toArray(), $combine_piles);
            }
        }
        $mat_ids = array_merge($combine_piles, $mat_ids);

        $complect_parts = $work_volume->shown_materials->where('material_type', 'complect')->whereIn('id', $mat_ids)->pluck('parts')->flatten();
        $materials = $work_volume->shown_materials->where('material_type', 'regular')->whereIn('id', $mat_ids);

        $mat_ids = $materials->pluck('id')->toArray();

        $wv_works = ManualWork::query();

        if (! empty($mat_ids)) {
            $wv_mat_works = $materials->shift()->manual->category->related_works;

            foreach ($materials as $mat) {
                $wv_mat_works = $wv_mat_works->intersect($mat->manual->category->related_works);
            }

            $wv_mat_works = $wv_mat_works->unique();
        }

        if ($request->is_tongue == 1) {
            $wv_works = $wv_works->whereIn('work_group_id', (new ManualWork())->tongue_groups);
        } else {
            $wv_works = $wv_works->whereIn('work_group_id', (new ManualWork())->pile_groups);
        }

        if ($request->q) {
            $wv_works = $wv_works->where('name', 'like', '%'.trim($request->q).'%')
                ->OrWhereHas('childs.child_work', function ($child_work) use ($request) {
                    $child_work->whereIn('work_group_id', $request->is_tongue ? (new ManualWork())->tongue_groups : (new ManualWork())->pile_groups);
                    $child_work->where('name', 'like', '%'.trim($request->q).'%');
                });
        }

        $used_materials = WorkVolumeWorkMaterial::whereIn('wv_material_id', $mat_ids ? $mat_ids : [])
            ->pluck('wv_work_id')
            ->toArray();

        $not_this_works = WorkVolumeWork::whereIn('id', $used_materials)
            ->pluck('manual_work_id')
            ->toArray();

        $wv_works = $wv_works
            ->whereNotIn('manual_works.id', $request->work_ids ? $request->work_ids : [])
            ->whereNotIn('manual_works.id', $not_this_works ? $not_this_works : []);

        $wv_works = isset($wv_mat_works) ? $wv_mat_works->intersect($wv_works->get()) : $wv_works->get();

        // find childs for each work
        $child_work_ids = [];
        foreach ($wv_works as $work) {
            if ($work->childs->count()) {
                $unused_childs = $work->childs
                    ->whereNotIn('child_work_id', $request->work_ids ? $request->work_ids : [])
                    ->whereNotIn('child_work_id', $not_this_works ? $not_this_works : [])
                    ->pluck('child_work_id')->toArray();

                foreach ($unused_childs as $child_work_id) {
                    $child_work_ids[] = $child_work_id;
                }
            }
        }

        if (count($child_work_ids)) {
            // get childs
            $childs = ManualWork::find($child_work_ids);
            // merge childs in collection
            $wv_works = $wv_works->merge($childs);
        }

        $results = [];
        foreach ($wv_works as $wv_work) {
            $results[] = [
                'id' => $wv_work->id,
                'text' => $wv_work->name.', '.$wv_work->unit,
            ];
        }

        return ['results' => $results];
    }

    public function get_material(Request $request)
    {
        $wv_materials = ManualMaterial::query();

        if (isset($request->filters['values'])) {
            $filter_count = (is_array($request->filters) || $request->filters instanceof Countable ? count($request->filters) : 0);
            $attr_filter = collect($request->filters['values'])->groupBy('attr_id');
        } else {
            $filter_count = 0;
        }

        if ($filter_count > 0) {
            $wv_materials->where('category_id', $request->filters['category']);
            foreach ($attr_filter as $attr_id => $values) {
                $wv_materials->whereHas('parameters', function ($q) use ($attr_id, $values) {
                    $q->where('attr_id', $attr_id)->whereIn('value', $values->pluck('value'));
                });
            }
        }

        if ($request->is_tongue and ! $filter_count) {
            $nodes = ManualNodes::query();
        }

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');

            if ($request->is_tongue and ! $filter_count) {
                $nodes = $nodes->where('name', 'like', '%'.trim($request->q).'%');
            }
        }

        $wv_materials = $wv_materials->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
            ->select('manual_materials.*', 'manual_material_categories.category_unit');

        if ($request->is_tongue) {
            $wv_materials->whereNotIn('manual_materials.category_id', [12, 14]);
        } elseif ($request->all) {
            //do nothing
        } else {
            $wv_materials->whereIn('manual_materials.category_id', [12]);
        }

        $wv_materials = $wv_materials->take(50)->get();

        if ($request->is_tongue and ! $filter_count) {
            $nodes = $nodes->with(['node_category', 'node_materials', 'node_materials.materials', 'node_materials.materials.parameters', 'node_materials.materials.category'])->get();
        }
        $results = [];
        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
                'unit' => $wv_material->category_unit,
            ];
        }

        if ($request->is_tongue and ! $filter_count) {
            foreach ($nodes as $node) {
                $results[] = [
                    'id' => $node->id,
                    'text' => $node->name,
                ];
            }
        }

        return ['results' => $results];
    }

    public function get_material_work(Request $request)
    {
        $mat_ids = $request->material_ids ? $request->material_ids : [];

        $work_volume = WorkVolume::find($request->work_volume_id);

        $combine_piles = [];
        foreach ($mat_ids as $key => $value) {
            if (strlen($value) > 10) {
                unset($mat_ids[$key]);
                $combine_piles = array_merge(WorkVolumeMaterial::where('combine_id', $value)->pluck('id')->toArray(), $combine_piles);
            }
        }

        $mat_ids = array_merge($combine_piles, $mat_ids);

        $complect_materials = $work_volume->shown_materials->where('material_type', 'complect');

        $wv_materials = $work_volume->materials()->where('material_type', 'regular');
        if ($request->q) {
            $search = $request->q;
            $wv_materials = $wv_materials->whereHasMorph('manual', [ManualMaterial::class], function ($manual) use ($search) {
                return $manual->where('name', 'like', '%'.trim($search).'%');
            });
        }
        if ($request->work_ids[0] != null) {
            $all_mat = ($wv_materials->pluck('manual_material_id'))->merge($complect_materials->pluck('parts')->flatten()->pluck('manual_material_id'))->unique();

            $material_ids = [];

            $manual_work = ManualWork::whereIn('id', (array) $request->work_ids)->where(function ($q) use ($all_mat) {
                $q->whereHas('related_categories', function ($categories) use ($all_mat) {
                    $categories->whereHas('materials', function ($materials) use ($all_mat) {
                        $materials->whereIn('id', $all_mat)->withTrashed();
                    });
                })->orWhereHas('normal_parent.related_categories', function ($categories) use ($all_mat) {
                    $categories->whereHas('materials', function ($materials) use ($all_mat) {
                        $materials->whereIn('id', $all_mat)->withTrashed();
                    });
                });
            })->first();

            if ($manual_work->normal_parent()->exists()) {
                $manual_work->load(['normal_parent.related_categories' => function ($categories) use ($all_mat) {
                    $categories->whereHas('materials', function ($materials) use ($all_mat) {
                        $materials->whereIn('id', $all_mat)->withTrashed();
                    });
                }]);
                $material_ids = $manual_work->normal_parent->related_categories()
                    ->whereHas('materials', function ($materials) use ($all_mat) {
                        $materials->whereIn('id', $all_mat)->withTrashed();
                    })->with(['materials' => function ($mats) use ($all_mat) {
                        $mats->whereIn('id', $all_mat)->withTrashed();
                    }])
                    ->get()->pluck('materials')->flatten()->pluck('id');
            } else {
                $manual_work->load(['related_categories' => function ($categories) use ($all_mat) {
                    $categories->whereHas('materials', function ($materials) use ($all_mat) {
                        $materials->whereIn('id', $all_mat)->withTrashed();
                    });
                }]);
                $material_ids = $manual_work->related_categories()
                    ->whereHas('materials', function ($materials) use ($all_mat) {
                        $materials->whereIn('id', $all_mat)->withTrashed();
                    })->with(['materials' => function ($mats) use ($all_mat) {
                        $mats->whereIn('id', $all_mat)->withTrashed();
                    }])
                    ->get()->pluck('materials')->flatten()->pluck('id');
            }

            $exist_works = WorkVolumeWork::whereIn('manual_work_id', $request->work_ids)->get();
            $exist_materials = [];
            foreach ($exist_works as $work) {
                $exist_materials[] = $work->materials->pluck('manual_material_id')->toArray();
            }

            if ($exist_materials) {
                $exist_materials = call_user_func_array('array_merge', $exist_materials);
            }

            $wv_materials = $wv_materials->whereIn('manual_material_id', $material_ids);
        }

        $used_materials = [];

        $used_works = WorkVolumeWork::where('work_volume_id', $request->work_volume_id)
            ->whereIn('manual_work_id', $request->work_ids ? $request->work_ids : [])
            ->pluck('id')
            ->unique()
            ->toArray();

        $used_materials = WorkVolumeWorkMaterial::whereIn('wv_work_id', $used_works)
            ->pluck('wv_material_id')
            ->unique()
            ->toArray();

        $wv_materials = $wv_materials
            ->whereNotIn('work_volume_materials.id', $mat_ids ? $mat_ids : [])
            ->whereNotIn('work_volume_materials.id', $used_materials)
            ->groupBy(DB::raw('ifnull(combine_id, work_volume_materials.id)'))
            ->get();

        $wv_materials = $wv_materials->unique(function ($item) {
            return $item['complect_id'] ?? $item['id'];
        });
        $results = [];
        foreach ($wv_materials as $material) {
            if ($material->complect_id) {
                $material = $material->complect;
            }
            if (! in_array($material->id, $mat_ids)) {
                $results[] = [
                    'id' => $material->combine_id ? $material->combine_id : $material->id,
                    'text' => $material->combine_id ? $material->combine_pile().'; '.$material->count.' '.$material->unit.';' : $material->name.'; '.$material->count.' '.$material->unit.';',
                ];
            }
        }

        return ['results' => $results];
    }

    public function create_composite_pile(Request $request, $wv_id)
    {
        DB::beginTransaction();

        $uniqid = uniqid();

        foreach ($request->piles as $pile_id) {
            $material = new WorkVolumeMaterial();

            $material->manual_material_id = $pile_id;
            $material->is_tongue = 0;
            $material->work_volume_id = $wv_id;
            $material->user_id = Auth::id();
            $material->is_our = 1;
            $material->count = $request->count;
            $material->combine_id = $uniqid;

            $material->price_per_one = ManualMaterial::findOrFail($material->manual_material_id)->buy_cost;
            $material->result_price = $material->price_per_one * $material->count;

            $material->save();
        }

        DB::commit();

        return redirect()->back();
    }

    public function get_composite_pile(Request $request)
    {
        if ($request->material_ids[0]) {
            $mat_value = ManualMaterialParameter::where('attr_id', 93)->where('mat_id', $request->material_ids[0])->first()->value;
            $mat_section = ManualMaterialParameter::where('attr_id', 95)->where('mat_id', $request->material_ids[0])->first()->value;
            $mat = ManualMaterial::findOrFail($request->material_ids[0]);
        }
        // dd($mat->name, strpos($mat->name, '('), strpos($mat->name, ')'), substr($mat->name, strpos($mat->name, '('), strpos($mat->name, ')')));
        $wv_materials = ManualMaterial::where('manual_materials.category_id', 14)
            ->leftJoin('manual_material_parameters', 'manual_material_parameters.mat_id', '=', 'manual_materials.id')
            ->where('manual_material_parameters.attr_id', 93);

        if ($request->material_ids[0]) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim(substr($mat->name, strpos($mat->name, '('), strpos($mat->name, ')'))).'%');
        }

        if ($request->q) {
            $wv_materials = $wv_materials->where('manual_materials.name', 'like', '%'.trim($request->q).'%');
        }

        if ($request->material_ids[0]) {
            $wv_materials = $wv_materials
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
                ->select('manual_materials.*', 'manual_material_categories.category_unit', 'manual_material_parameters.attr_id', 'manual_material_parameters.mat_id', 'manual_material_parameters.value')
                ->pluck('manual_materials.id')
                ->unique();
        } else {
            $wv_materials = $wv_materials
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', '=', 'manual_materials.category_id')
                ->select('manual_materials.*', 'manual_material_categories.category_unit', 'manual_material_parameters.attr_id', 'manual_material_parameters.mat_id', 'manual_material_parameters.value');
        }

        if ($request->material_ids[0]) {
            $wv_materials = ManualMaterial::whereIn('manual_materials.id', $wv_materials)
                ->leftJoin('manual_material_parameters', 'manual_material_parameters.mat_id', '=', 'manual_materials.id')
                ->where('manual_material_parameters.attr_id', 95)
                ->select('manual_materials.*', 'manual_material_parameters.attr_id', 'manual_material_parameters.mat_id', 'manual_material_parameters.value');

            $wv_materials = $wv_materials->where('manual_material_parameters.value', $mat_section);
        }
        $wv_materials = $wv_materials->take(100)->get()->unique();

        $results = [];
        foreach ($wv_materials as $wv_material) {
            $results[] = [
                'id' => $wv_material->id,
                'text' => $wv_material->name,
            ];
        }

        return ['results' => $results];
    }

    public function get_work_count(Request $request)
    {
        $mat_ids = $request->material_ids ? $request->material_ids : [];

        $work_volume = WorkVolume::find($request->work_volume_id);
        $worksWithCount = (new ManualWork())->worksWithCount();

        $combine_piles = [];
        foreach ($mat_ids as $key => $value) {
            if (strlen($value) > 10) {
                unset($mat_ids[$key]);
                $combine_piles = array_merge(WorkVolumeMaterial::where('combine_id', $value)->pluck('id')->toArray(), $combine_piles);
            }
        }

        $mat_ids = array_merge($combine_piles, $mat_ids);

        if ($mat_ids || $request->material_ids) {
            $manual_material = $work_volume->shown_materials->find($mat_ids);

            $manual_works = ManualWork::whereIn('id', $request->wv_work_id)
                ->orWhereHas('childs', function ($child_work) use ($request) {
                    $child_work->whereIn('child_work_id', $request->wv_work_id);
                })->get();

            $count_work_1 = [];

            foreach ($manual_works->where('is_copied', 0) as $work) {
                $count_work_1[$work->id.' '.$work->name] = 0;
            }

            $count_work_2 = $count_work_1;

            if ($manual_material->count() > 0 and $manual_works->count() > 0) {
                // dd($manual_material);
                foreach ($manual_material as $material) {
                    foreach ($manual_works->where('is_copied', 0) as $work) {
                        if ($material->unit == $work->unit) {
                            if ($material->combine_id) {
                                $count_work_1[$work->id.' '.$work->name] += $material->count / 2;
                            } else {
                                $count_work_1[$work->id.' '.$work->name] += $material->count;
                            }
                        } else {
                            if ($material->unit != $material->category_unit) {
                                $count_work_1[$work->id.' '.$work->name] += round($material->count / ($material->manual->convert_to($material->unit)->value ?? 1), 3);
                            } else {
                                $count_work_1[$work->id.' '.$work->name] += round($material->count * ($material->manual->convert_to($work->unit)->value ?? 1), 3);
                            }
                        }
                    }
                }

                // foreach ($manual_material as $material) {
                //     foreach ($material->parameters as $parameter) {
                //         foreach ($manual_works->where('is_copied', 0) as $work) {
                //             if ($parameter->unit == $work->unit) {
                //                 $count_work_2[$work->id . ' ' . $work->name] += $material->count * $parameter->value;
                //             }
                //         }
                //     }
                // }
            }

            foreach ($count_work_1 as $key => $value) {
                if ($value == 0) {
                    unset($count_work_1[$key]);
                }
            }

            $result_arr = [];
            foreach (array_merge($count_work_2, $count_work_1) as $key => $value) {
                $result_arr[(int) $key] = $value;
            }
            $result = [];

            foreach ($manual_works->pluck('id')->toArray() as $key_1 => $value_1) {
                foreach ($result_arr as $key_2 => $value_2) {
                    if ($value_1 == $key_2) {
                        $result[$key_1] = round($value_2, in_array($value_1, $worksWithCount) ? 0 : 3);
                    }
                }
            }
        } else {
            return response()->json(false);
        }

        return response()->json($result);
    }

    // WorkVolumeMaterialRequest
    public function attach_material(Request $request, $wv_id)
    {
        DB::beginTransaction();
        // if ($request['attributes']) {
        //     $material = ManualMaterial::getModel()->createMaterial($request['attributes'], $request->category_id);
        // }

        // if ($request['new_logic']) {
        //     $request->manual_material_id = $material->id;
        //     $request->count = $request['count'];
        // }

        if (! $request->is_node) {
            $material = new WorkVolumeMaterial();
            $material->is_tongue = $request->is_tongue;
            $material->manual_material_id = $request->manual_material_id;
            $material->count = $request->count;
            $material->work_volume_id = $wv_id;
            $material->user_id = Auth::id();
            $material->is_our = 1;
            $material->unit = $request->unit;
            $material->material_type = 'regular';
            $material->price_per_one = ManualMaterial::findOrFail($request->manual_material_id)->buy_cost;
            $material->result_price = $material->price_per_one * $material->count;

            $material->save();
        } else {
            $node = ManualNodes::where('id', $request->manual_material_id)->with(['node_category', 'node_materials', 'node_materials.materials', 'node_materials.materials.category'])->first();
            $safety_factor = $node->node_category->safety_factor / 100;

            foreach ($node->node_materials as $node_material) {
                $material = new WorkVolumeMaterial();

                $material->manual_material_id = $node_material->manual_material_id;
                $material->count = $node_material->count * $request->count * (1 + $safety_factor);
                $material->is_our = 1;
                $material->time = $request->time;
                $material->is_tongue = $request->is_tongue;
                $material->work_volume_id = $wv_id;
                $material->user_id = Auth::id();
                // $material->combine_id = uniqid();

                $material->price_per_one = ManualMaterial::findOrFail($material->manual_material_id)->buy_cost;
                $material->result_price = $material->price_per_one * $material->count;

                $material->save();

                if ($node->is_compact_wv) {
                    WorkVolumeMaterialComplect::create([
                        'name' => $node->name,
                        'wv_material_id' => $material->id,
                        'work_volume_id' => $wv_id,
                    ]);
                }
            }
        }

        DB::commit();

        return back();
    }

    public function detach_material(Request $request)
    {
        DB::beginTransaction();

        $work_volume = WorkVolume::findOrFail($request->wv_id);

        $material = $work_volume->materials()->where('id', $request->mat_id)->get();

        if ($material->first()->combine_id) {
            $parts = $work_volume->materials->where('combine_id', $material->first()->combine_id);
        } else {
            $parts = $material;
        }

        foreach ($parts as $part) {
            $related_works = $part->works;

            foreach ($related_works as $work) {

                $work->materials()->detach($part);
                $work->refresh();

                if ($work->materials()->count() == 0) {
                    $work->delete();
                } else {
                    $deleted_count = $part->count;
                    if ($work->unit != $part->unit) {
                        $deleted_count = $part->convertCountTo($work->manual->unit);
                    }

                    if ($deleted_count < $work->count) {
                        $work->count -= $deleted_count;
                    }

                    $work->save();
                }
            }

            $part->delete();
        }

        DB::commit();

        return response()->json(true);
    }

    public function request_store(WorkVolumeReqRequest $request, $project_id)
    {
        DB::beginTransaction();

        if ($request->axios) {
            return response()->json(true);
        }

        if ($request->from_task_17) {
            // close task after new request create from task17
            $task = Task::find($request->from_task_17);
            $task->result = 1;
            $task->final_note = $task->descriptions[$task->status].Auth::user()->full_name.$task->results[$task->status][$task->result];
            $task->is_solved = 1;
            $task->save();

            $request_wv = WorkVolumeRequest::find($task->target_id);
        }

        if ($request->from_task_18) {
            $task_18 = Task::find($request->from_task_18);

            $request->work_volume_tongue_id = $task_18->target_id;
        }

        $project = Project::find($project_id);
        $pileTasks = Task::where('project_id', $project_id)->where('target_id', $request->work_volume_pile_id)->where('status', 4)->where('is_solved', 0)->count();

        if ($request->add_tongue) {
            $wv_tongue = new WorkVolume();

            if ($request->work_volume_tongue_id == 'new') {
                if (WorkVolume::whereProjectId($project->id)->whereType(0)->whereOption($request->option_tongue)->count()) {
                    return back()->with('name_repeat', true);
                }

                $wv_tongue_req_name = 'Заявка на расчёт объемов работ (шпунтовое направление)';
                $version = 1;
                $is_new_tongue = true;
            } else {
                $is_new_tongue = false;
                $wv_tongue_req_name = 'Заявка на редактирование объемов работ (шпунтовое направление)';
                $wv_tongue_previos = WorkVolume::findOrFail($request->work_volume_tongue_id ? $request->work_volume_tongue_id : $request_wv->work_volume_id);
                if ($wv_tongue_previos->status == 1) {
                    $wv_tongue = $wv_tongue_previos;
                    $version = $wv_tongue_previos->version;
                } else {
                    $is_new_tongue = true;

                    $wv_tongue_previos->status = 3;
                    $wv_tongue_previos->save();
                    $version = $wv_tongue_previos->version + 1;
                }
            }
            $wv_tongue->version = $version;
            $wv_tongue->user_id = Auth::user()->id;
            $wv_tongue->depth = isset($wv_tongue_previos->depth) ? $wv_tongue_previos->depth : 0;
            $wv_tongue->project_id = $project_id;
            $wv_tongue->status = 1;
            $wv_tongue->type = 0;
            $wv_tongue->option = isset($wv_tongue_previos->option) ? $wv_tongue_previos->option : $request->option_tongue;
            $wv_tongue->save();

            $wv_tongue->save_request($request->tongue_description, $wv_tongue_req_name, $request->tongue_documents, $request->project_documents_tongue);
            $wv_tongue = $wv_tongue->fresh();
            // $wv_tongue->load('requests');

            // block for task 18 decline
            $tasks_18 = Task::where('project_id', $project_id)->where('target_id', $wv_tongue->id)->where('status', 18)->where('is_solved', 0)->with('project.object')->get();

            if ($tasks_18->isNotEmpty()) {
                foreach ($tasks_18 as $item) {
                    if ($request->has('from_task_18')) {
                        $item->result = 2;
                        $item->final_note = $request->tongue_description ?? ($item->descriptions[$item->status].$item->results[$item->status][$item->result]);
                        $notify_name = 'Задача «'.$item->name.'» закрыта';
                    } else {
                        $notify_name = 'Задача «'.$item->name.'» закрыта, так как появилась новая заявка на ОР';
                    }

                    $item->create_notify($notify_name, 3);

                    $item->solve();
                }

                $wv_requests = $wv_tongue->requests()->whereStatus(2)->get();

                if (Task::whereStatus(17)->whereIn('target_id', $wv_requests->pluck('id'))->count() != $wv_requests->count()) {
                    $task17 = new Task();
                    $task17->project_id = $wv_tongue->project_id;
                    $task17->name = 'Обработка заявки на ОР шпунтового направления'.' по проекту '.($project->name);
                    $task17->responsible_user_id = $wv_requests->last()->user_id;
                    $task17->contractor_id = $project->contractor_id;
                    $task17->final_note = $request->comment;
                    $task17->target_id = $wv_requests->last()->id; // request id
                    $task17->expired_at = Carbon::now()->addHours(10);
                    $task17->prev_task_id = Task::where('target_id', $wv_requests->last()->work_volume_id)->where('status', ($wv_requests->last()->tongue_pile ? 4 : 3))->orderByDesc('id')->first()->id ?? null;
                    $task17->status = 17;
                    $task17->save();

                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на задачу: '.$task17->task_route();
                    $notification->update([
                        'name' => 'Новая задача «'.$task17->name.'»',
                        'task_id' => $task17->id,
                        'user_id' => $task17->responsible_user_id,
                        'contractor_id' => $task17->contractor_id ?? null,
                        'project_id' => $task17->project_id ?? null,
                        'object_id' => isset($task17->project->object->id) ? $task17->project->object->id : null,
                        'type' => 23,
                    ]);
                }
            }

            $agreeTongueTask = Task::where('project_id', $project_id)->where('target_id', $wv_tongue->id)->where('status', 18)->where('is_solved', 0)->count();
            $exist_reps_user = Task::where('project_id', $project_id)->where('status', 14)->where('is_solved', 0)->count();
            if (! $agreeTongueTask and ! $exist_reps_user) {
                $tongueResp = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 4)->first();

                $tongueTask = new Task();
                if (! $tongueResp) {
                    $tongueTask->name = 'Назначение ответственного за ОР (шпунт)';
                    $tongueTask->status = 14;
                    $tongueTask->responsible_user_id = Group::find(50)->getUsers()->first()->id; // [hardcoded]
                    $tongueTask->expired_at = Carbon::now()->addHours(3);
                } else {
                    $prev_task = $wv_tongue->tasks()->orderByDesc('id')->first() ?? null;
                    $tongueTask->name = 'Расчёт объемов (шпунтовое направление)';
                    $tongueTask->status = 3;
                    $tongueTask->responsible_user_id = $tongueResp->user_id;
                    $tongueTask->prev_task_id = $prev_task->id ?? null;
                    $tongueTask->expired_at = Carbon::now()->addHours(24);
                }

                $tongueTask->target_id = $wv_tongue->id;
                $tongueTask->project_id = $project_id;
                $tongueTask->contractor_id = $project->contractor_id;

                $tongueTask->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: '.$tongueTask->task_route();
                $notification->update([
                    'name' => 'Новая задача «'.$tongueTask->name.'»',
                    'task_id' => $tongueTask->id,
                    'user_id' => $tongueTask->responsible_user_id,
                    'contractor_id' => $tongueTask->project_id ? Project::find($tongueTask->project_id)->contractor_id : null,
                    'project_id' => $tongueTask->project_id ? $tongueTask->project_id : null,
                    'object_id' => $tongueTask->project_id ? Project::find($tongueTask->project_id)->object_id : null,
                    'type' => $tongueTask->status == 3 ? 21 : 24,
                ]);
            } else {
                $lastTask = $wv_tongue->tasks()->whereStatus(3)->whereResponsibleUserId(Auth::id())->orderByDesc('id')->first() ?? null;

                if ($lastTask) {
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на задачу: '.$lastTask->task_route();
                    $notification->update([
                        'name' => $lastTask->name,
                        'task_id' => $lastTask->id,
                        'user_id' => $lastTask->responsible_user_id,
                        'contractor_id' => $lastTask->project_id ? Project::find($lastTask->project_id)->contractor_id : null,
                        'project_id' => $lastTask->project_id ? $lastTask->project_id : null,
                        'object_id' => $lastTask->project_id ? Project::find($lastTask->project_id)->object_id : null,
                        'type' => 23,
                    ]);
                }
            }
        }

        if ($request->add_pile) {

            $wv_pile = new WorkVolume();

            if ($request->work_volume_pile_id == 'new') {
                if (WorkVolume::whereProjectId($project->id)->whereType(1)->whereOption($request->option_pile)->count()) {
                    return abort(403, 'Объем работ с таким наименованием уже существует.');
                }

                $wv_pile_req_name = 'Заявка на создание объемов работ (свайное направление)';
                $version = 1;
                $is_new_pile = true;
            } else {
                $is_new_pile = false;
                $wv_pile_req_name = 'Заявка на редактирование объемов работ (свайное направление)';
                $version = 1;
                $wv_pile_previos = WorkVolume::findOrFail($request->work_volume_pile_id);
                if ($wv_pile_previos->status == 1) {
                    $wv_pile = $wv_pile_previos;
                    $version = $wv_pile_previos->version;
                } else {
                    $is_new_pile = true;
                    $wv_pile_previos->status = 3;
                    $wv_pile_previos->save();
                    $version = $wv_pile_previos->version + 1;
                }
            }
            $wv_pile->version = $version;
            $wv_pile->user_id = Auth::user()->id;
            $wv_pile->project_id = $project_id;
            $wv_pile->status = 1;
            $wv_pile->type = 1;
            $wv_pile->option = isset($wv_pile_previos->option) ? $wv_pile_previos->option : $request->option_pile;
            $wv_pile->save();

            ProjectResponsibleUser::firstOrCreate(
                ['role' => 3, 'project_id' => $project_id], ['user_id' => Group::find(54/*35*/)->getUsers()->first()->id]
            );

            ProjectResponsibleUser::firstOrCreate(
                ['role' => 1, 'project_id' => $project_id], ['user_id' => Group::find(54/*35*/)->getUsers()->first()->id]
            );

            $wv_pile->save_request($request->pile_description, $wv_pile_req_name, $request->pile_documents, $request->project_documents_pile);
            $wv_pile = $wv_pile->fresh();

            if ($pileTasks == 0) {
                $prev_task = $wv_pile->tasks()->orderByDesc('id')->first() ?? null;

                $pileTask = new Task();
                $pileTask->project_id = $project_id;
                $pileTask->name = 'Расчёт объемов (свайное направление)';
                $pileTask->status = 4;
                $pileTask->responsible_user_id = 27; //Бондарева user id hardcoded
                $pileTask->prev_task_id = $prev_task->id ?? null;
                $pileTask->contractor_id = Project::find($project_id)->contractor_id;
                $pileTask->expired_at = Carbon::now()->addHours(24);
                $pileTask->target_id = $wv_pile->id;

                $pileTask->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: '.$pileTask->task_route();
                $notification->update([
                    'name' => 'Новая задача «'.$pileTask->name.'»',
                    'task_id' => $pileTask->id,
                    'user_id' => $pileTask->responsible_user_id,
                    'contractor_id' => $pileTask->project_id ? Project::find($pileTask->project_id)->contractor_id : null,
                    'project_id' => $pileTask->project_id ? $pileTask->project_id : null,
                    'object_id' => $pileTask->project_id ? Project::find($pileTask->project_id)->object_id : null,
                    'type' => 22,
                ]);
            } else {
                $lastTask = Task::where('project_id', $project_id)->where('target_id', $wv_pile->id)->where('is_solved', 0)->get()->last();
                if ($lastTask) {
                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на задачу: '.$lastTask->task_route();
                    $notification->update([
                        'name' => 'Новая задача «'.$lastTask->name.'»',
                        'task_id' => $lastTask->id,
                        'user_id' => $lastTask->responsible_user_id,
                        'contractor_id' => $lastTask->project_id ? Project::find($lastTask->project_id)->contractor_id : null,
                        'project_id' => $lastTask->project_id ? $lastTask->project_id : null,
                        'object_id' => $lastTask->project_id ? Project::find($lastTask->project_id)->object_id : null,
                        'type' => 23,
                    ]);
                }
            }
        }
        if ($request->add_tongue) {
            if ($request->work_volume_tongue_id != 'new' && $is_new_tongue) {
                $wv_tongue_previos->load('shown_materials.parts');
                $last_commercial_offer = CommercialOffer::where('project_id', $project_id)->where('work_volume_id', $wv_tongue_previos->id)->get()->last();
                $works = WorkVolumeWork::where('work_volume_id', $wv_tongue_previos->id)->get();
                $works_materials = WorkVolumeWorkMaterial::whereIn('wv_work_id', $works->pluck('id'))->get();
                $materials = $wv_tongue_previos->shown_materials;

                $old_relations = [];
                foreach ($works_materials as $item) {
                    $old_relations[] = [$item->wv_material_id => $item->wv_work_id];
                }

                $new_relation = [];
                foreach ($materials as $material) {
                    if ($material->material_type != 'complect') {
                        $new_material = $material->replicate();

                        $new_material->work_volume_id = $wv_tongue->id;
                        $new_material->save();

                        foreach ($old_relations as $item) {
                            $material_id = key($item);

                            if ($material_id == $material->id) {
                                $new_relation[] = [$new_material->id => $item[$material_id]];
                            }
                        }
                    } else {
                        $new_complect = $material->replicate();

                        $new_complect->work_volume_id = $wv_tongue->id;
                        $new_complect->save();

                        foreach ($material->parts as $part) {
                            $new_material = $part->replicate();

                            $new_material->work_volume_id = $wv_tongue->id;
                            $new_material->complect_id = $new_complect->id;
                            $new_material->save();

                            foreach ($old_relations as $item) {
                                $material_id = key($item);

                                if ($material_id == $part->id) {
                                    $new_relation[] = [$new_material->id => $item[$material_id]];
                                }
                            }
                        }
                    }
                }

                $result = [];
                foreach ($works as $work) {
                    $new_work = $work->replicate();

                    if ($last_commercial_offer) {
                        if ($last_commercial_offer->commercial_offer_works()->count() > 0) {
                            $com_offer_work = $last_commercial_offer->works()->where('work_volume_work_id', $work->id)->first();

                            $new_work->count = $com_offer_work ? $com_offer_work->count : $work->count;
                            $new_work->term = $com_offer_work ? $com_offer_work->term : $work->term;
                            $new_work->price_per_one = $com_offer_work ? $com_offer_work->price_per_one : $work->price_per_one;
                            $new_work->result_price = $com_offer_work ? $com_offer_work->result_price : $work->result_price;
                            $new_work->subcontractor_file_id = $com_offer_work ? $com_offer_work->subcontractor_file_id : $work->subcontractor_file_id;
                            $new_work->is_hidden = $com_offer_work ? $com_offer_work->is_hidden : $work->is_hidden;
                            $new_work->order = $com_offer_work ? $com_offer_work->order : $work->order;
                        }
                    }
                    $new_work->work_volume_id = $wv_tongue->id;
                    $new_work->save();

                    foreach ($new_relation as $item) {
                        $work_id = current($item);

                        if ($work_id == $work->id) {
                            $result[] = [key($item) => $new_work->id];
                        }
                    }
                }

                foreach ($result as $item) {
                    WorkVolumeWorkMaterial::create(['wv_work_id' => current($item), 'wv_material_id' => key($item)]);
                }
            }

            $offers_count = CommercialOffer::where('project_id', $project_id)->where('work_volume_id', $wv_tongue_previos->id ?? 0)->where('status', '!=', 3)->update(['status' => 3]);

            // $com_offer_double = CommercialOffer::where('project_id', $project_id)->where('is_tongue', 2)->get()->last();

            $offers_id = CommercialOffer::where('project_id', $project_id)->where('work_volume_id', $wv_tongue_previos->id ?? 0)->pluck('id')->toArray();

            $tasks = Task::where('project_id', $project_id)->whereIn('status', [5, 6, 12, 15, 16])->whereIn('target_id', $offers_id)->get();

            foreach ($tasks as $item) {
                if ($item->responsible_user_id != 6) {
                    $item->create_notify('Задача «'.$item->name.'» закрыта', 3);
                }

                $item->solve();
            }

            $commercial_offer_requests = CommercialOfferRequest::where('project_id', $project_id)->whereIn('commercial_offer_id', $offers_id)->where('status', 0)->update(['status' => 2]);
        }
        if ($request->add_pile) {
            if ($request->work_volume_pile_id != 'new' && $is_new_pile) {
                $works = WorkVolumeWork::where('work_volume_id', $wv_pile_previos->id)->get();
                $last_commercial_offer = CommercialOffer::where('project_id', $project_id)->where('work_volume_id', $wv_pile_previos->id)->get()->last();
                $works_materials = WorkVolumeWorkMaterial::whereIn('wv_work_id', $works->pluck('id'))->get();
                $materials = WorkVolumeMaterial::where('work_volume_id', $wv_pile_previos->id)->get();

                $old_relations = [];
                foreach ($works_materials as $item) {
                    $old_relations[] = [$item->wv_material_id => $item->wv_work_id];
                }

                $new_relation = [];
                foreach ($materials as $material) {
                    $new_material = $material->replicate();

                    $new_material->work_volume_id = $wv_pile->id;
                    $new_material->save();

                    foreach ($old_relations as $item) {
                        $material_id = key($item);

                        if ($material_id == $material->id) {
                            $new_relation[] = [$new_material->id => $item[$material_id]];
                        }
                    }
                }

                $result = [];
                foreach ($works as $work) {
                    $new_work = $work->replicate();

                    if ($last_commercial_offer) {
                        if ($last_commercial_offer->commercial_offer_works()->count() > 0) {
                            $com_offer_work = $last_commercial_offer->works()->where('work_volume_work_id', $work->id)->first();

                            $new_work->count = $com_offer_work ? $com_offer_work->count : $work->count;
                            $new_work->term = $com_offer_work ? $com_offer_work->term : $work->term;
                            $new_work->price_per_one = $com_offer_work ? $com_offer_work->price_per_one : $work->price_per_one;
                            $new_work->result_price = $com_offer_work ? $com_offer_work->result_price : $work->result_price;
                            $new_work->subcontractor_file_id = $com_offer_work ? $com_offer_work->subcontractor_file_id : $work->subcontractor_file_id;
                            $new_work->is_hidden = $com_offer_work ? $com_offer_work->is_hidden : $work->is_hidden;
                            $new_work->order = $com_offer_work ? $com_offer_work->order : $work->order;
                        }
                    }
                    $new_work->work_volume_id = $wv_pile->id;
                    $new_work->save();

                    foreach ($new_relation as $item) {
                        $work_id = current($item);

                        if ($work_id == $work->id) {
                            $result[] = [key($item) => $new_work->id];
                        }
                    }
                }

                foreach ($result as $item) {
                    WorkVolumeWorkMaterial::create(['wv_work_id' => current($item), 'wv_material_id' => key($item)]);
                }
            }

            $requests = WorkVolumeRequest::where('work_volume_id', 0)->where('tongue_pile', 1)
                ->where('project_id', $project_id)
                ->update(['work_volume_id' => $wv_pile->id]);

            $offers_count = CommercialOffer::where('project_id', $project_id)->where('is_tongue', 0)->update(['status' => 3]);

            $com_offer_double = CommercialOffer::where('project_id', $project_id)->where('is_tongue', 2)->get()->last();

            $offers_id = CommercialOffer::where('project_id', $project_id)->where('is_tongue', 0)->pluck('id')->toArray();

            $tasks = Task::where('project_id', $project_id)->whereIn('status', [5, 6, 12, 15, 16])->whereIn('target_id', $offers_id)->where('is_solved', 0)->get();

            foreach ($tasks as $item) {
                Notification::create([
                    'name' => 'Задача «'.$item->name.'» закрыта',
                    'task_id' => $item->id,
                    'user_id' => $item->responsible_user_id,
                    'contractor_id' => $item->project_id ? Project::find($item->project_id)->contractor_id : null,
                    'project_id' => $item->project_id ? $item->project_id : null,
                    'object_id' => $item->project_id ? Project::find($item->project_id)->object_id : null,
                    'type' => 3,
                ]);

                $item->solve();
            }

            $commercial_offer_requests = CommercialOfferRequest::where('project_id', $project_id)->whereIn('commercial_offer_id', $offers_id)->where('status', 0)->update(['status' => 2]);
        }

        // block for new KP statuses logic (like in ProjectCommercialOfferController)
        // if (isset($com_offer_double)) {
        //     if ($com_offer_double->status != 3 and ($request->has('add_tongue') && !$request->has('add_pile')) or ($request->has('add_pile') && !$request->has('add_tongue'))) {
        //         $type = $request->has('add_tongue') ? 0 : 1;
        //         $com_offer_for_update = CommercialOffer::where('project_id', $project_id)->where('is_tongue', $type)->get()->last();
        //         !isset($com_offer_for_update) ?: $com_offer_for_update->update(['status' => 5]);
        //     }
        // }

        if (isset($com_offer_double)) {
            $com_offer_double->decline();
        }

        if ($project->status < 2 || $project->status == 5) {
            Project::where('id', $project->id)->update(['status' => 2]);
        }

        DB::commit();

        return back()->with('work_volume', true);
    }

    public function request_update(Request $request)
    {
        DB::beginTransaction();

        $wv_request = WorkVolumeRequest::findOrFail($request->wv_request_id);
        $work_volume = WorkVolume::find($wv_request->work_volume_id);

        if (! isset($request->comment)) {
            session(['edited_wv_id' => $work_volume->id]);
            session(['edited_wv_request_id' => $request->wv_request_id]);
            session(['edit_start' => Carbon::now()]);

            return back();
        } else {
            if (isset($request->status)) {
                $wv_request->status = $request->status == 'confirm' ? 1 : 2;
                $wv_request->result_comment = $request->comment;
            }

            $user = auth()->user()->long_full_name;

            Notification::create([
                'name' => ('Пользователь '.$user.' '.
                ($request->status == 'confirm' ? 'подтвердил(а) ' : 'отклонил(а) ').
                    'заявку на редактирование ОР '.($wv_request->tongue_pile ? 'свайного' : 'шпунтового')
                .' направления версии '.$work_volume->version.
                    ' по проекту '.Project::find($wv_request->project_id)->name),
                'user_id' => $wv_request->user_id,
                'contractor_id' => Project::find($wv_request->project_id)->contractor_id,
                'project_id' => $wv_request->project_id,
                'object_id' => Project::find($wv_request->project_id)->object_id,
                'target_id' => $request->wv_request_id,
                'status' => 3,
                'type' => 27,
            ]);

            if ($request->documents) {
                foreach ($request->documents as $document) {
                    $file = new WorkVolumeRequestFile();

                    $mime = $document->getClientOriginalExtension();
                    $file_name = 'project-'.$wv_request->project_id.'/work_volume'.$wv_request->work_volume_id.'request_file-'.uniqid().'.'.$mime;

                    Storage::disk('work_volume_request_files')->put($file_name, File::get($document));

                    FileEntry::create([
                        'filename' => $file_name,
                        'size' => $document->getSize(),
                        'mime' => $document->getClientMimeType(),
                        'original_filename' => $document->getClientOriginalName(),
                        'user_id' => Auth::user()->id,
                    ]);

                    $file->file_name = $file_name;
                    $file->request_id = $wv_request->id;
                    $file->is_result = 1;
                    $file->original_name = $document->getClientOriginalName();

                    $file->save();
                }
            }

            if ($request->project_documents) {
                $project_docs = ProjectDocument::whereIn('id', $request->project_documents)->get();

                foreach ($request->project_documents as $document_id) {
                    $file = new WorkVolumeRequestFile();

                    $file->file_name = $project_docs->where('id', $document_id)->first()->file_name;
                    $file->request_id = $wv_request->id;
                    $file->is_result = 1;
                    $file->original_name = $project_docs->where('id', $document_id)->first()->name;
                    $file->is_proj_doc = 1;

                    $file->save();
                }
            }

            $wv_request->save();

            $work_volume_requests = WorkVolumeRequest::where('work_volume_id', $work_volume->id)->get();
            // block for WV decline after decline all wv_requests
            if ($request->status == 'reject') {
                $project = Project::find($work_volume->project_id);
                $declined_requests = WorkVolumeRequest::where('work_volume_id', $work_volume->id)->where('status', 2)->count();
                if (! $work_volume_requests->where('status', 0)->count()) {
                    if ($work_volume->type == 1) {
                        $work_volume->decline();
                    } else {
                        $task = Task::where('responsible_user_id', Auth::id())->where('target_id', $work_volume->id)->where('status', $work_volume->type ? 4 : 3)->where('project_id', $project->id)->where('is_solved', 0)->get()->first();
                        ! isset($task) ?: $task->solve();

                        // make new task for Директор по развитию
                        if (! Task::where('project_id', $work_volume->project_id)->where('target_id', $work_volume->id)->where('status', 18)->where('is_solved', 0)->count()) {
                            $task18 = new Task();
                            $task18->project_id = $work_volume->project_id;
                            $task18->name = 'Контроль выполнения ОР шпунтового направления по проекту '.($project->name);
                            $task18->responsible_user_id = Group::find(50)->getUsers()->first()->id; //Директор по развитию [hardcoded]
                            $task18->contractor_id = $project->contractor_id;
                            $task18->target_id = $work_volume->id; // WV id
                            $task18->expired_at = $this->addHours(24);
                            $task18->final_note = $request->comment;
                            $task18->prev_task_id = Task::where('target_id', $work_volume->id)->where('status', ($work_volume->type ? 4 : 3))->orderByDesc('id')->first()->id ?? null;
                            $task18->status = 18;
                            $task18->save();

                            $notification = new Notification();
                            $notification->save();
                            $notification->additional_info = ' Ссылка на задачу: '.$task18->task_route();
                            $notification->update([
                                'name' => 'Новая задача «'.$task18->name.'»',
                                'task_id' => $task18->id,
                                'user_id' => $task18->responsible_user_id,
                                'contractor_id' => $task18->contractor_id,
                                'project_id' => $task18->project_id,
                                'object_id' => $project->object_id,
                                'type' => 25,
                            ]);
                        }
                    }
                } elseif ($work_volume->type == 0) {
                    $project = Project::find($work_volume->project_id);

                    $task17 = new Task();
                    $task17->project_id = $work_volume->project_id;
                    $task17->name = 'Обработка заявки на ОР'.($wv_request->tongue_pile ? ' свайного направления' : ' шпунтового направления').' по проекту '.($project->name);
                    $task17->responsible_user_id = $wv_request->user_id;
                    $task17->contractor_id = $project->contractor_id;
                    $task17->final_note = $request->comment;
                    $task17->target_id = $wv_request->id; // request id
                    $task17->expired_at = $this->addHours(10);
                    $task17->prev_task_id = Task::where('target_id', $wv_request->work_volume_id)->where('status', ($wv_request->tongue_pile ? 4 : 3))->where('is_solved', 0)->first()->id ?? null;
                    $task17->status = 17;
                    $task17->save();

                    $notification = new Notification();
                    $notification->save();
                    $notification->additional_info = ' Ссылка на задачу: '.$task17->task_route();
                    $notification->update([
                        'name' => 'Новая задача «'.$task17->name.'»',
                        'task_id' => $task17->id,
                        'user_id' => $task17->responsible_user_id,
                        'contractor_id' => $task17->contractor_id,
                        'project_id' => $task17->project_id,
                        'object_id' => $task17->object_id,
                        'type' => 23,
                    ]);
                }
            }
        }

        DB::commit();

        return back();
    }

    public function request_wv_update(Request $request)
    {
        if (! WorkVolumeWork::where('work_volume_id', session()->get('edited_wv_id'))->first()) {
            return back()->with('wv', 'Заполните объём работ');
        }

        DB::beginTransaction();

        $work_volume = WorkVolume::find(session()->get('edited_wv_id'));
        $wv_request = WorkVolumeRequest::findOrFail(session()->get('edited_wv_request_id'));

        $wv_request->status = 1;
        $wv_request->result_comment = $request->confirm_comment;

        $wv_request->save();

        $user = auth()->user()->long_full_name;

        Notification::create([
            'name' => ('Пользователь '.$user.' '.'подтвердил(а) заявку на редактирование ОР '.
                ($wv_request->tongue_pile ? 'свайного' : 'шпунтового')
                .' направления версии '.$work_volume->version.' по проекту '.Project::find($wv_request->project_id)->name),
            'user_id' => $wv_request->user_id,
            'contractor_id' => Project::find($wv_request->project_id)->contractor_id,
            'project_id' => $wv_request->project_id,
            'object_id' => Project::find($wv_request->project_id)->object_id,
            'target_id' => session()->get('edited_wv_request_id'),
            'status' => 3,
            'type' => 27,
        ]);

        session()->forget(['edited_wv_id', 'edited_wv_request_id', 'edit_start']);

        // block for Начальник ПТО in request accept
        $work_volume_requests = WorkVolumeRequest::where('work_volume_id', $work_volume->id)->get();
        if (! $work_volume_requests->where('status', 0)->count() and $wv_request->wv->type == 0) {
            if (! Task::where('project_id', $work_volume->project_id)->where('target_id', $work_volume->id)->where('status', 18)->where('is_solved', 0)->count()) {
                $prev_task = $work_volume->tasks()->whereStatus(3)->whereResponsibleUserId(Auth::id())->orderByDesc('id')->first();
                if ($prev_task) {
                    $prev_task->final_note = $wv_request->result_comment;
                    $prev_task->solve_n_notify();
                }
                $project = Project::find($work_volume->project_id);
                $projectResponsibleForCommercialOfferUserId = ProjectResponsibleUser::query()
                    ->where('project_id', $project->id)
                    ->whereIn('role', [1, 2])
                    ->first()->user_id;

                $task18 = new Task();
                $task18->project_id = $project->id;
                $task18->name = 'Контроль выполнения ОР шпунтового направления по проекту '.($project->name);
                $task18->responsible_user_id = $projectResponsibleForCommercialOfferUserId;
                //    старый вариант
                //    $task18->responsible_user_id = Group::find(50)->getUsers()->first()->id; //Директор по развитию [hardcoded]
                $task18->contractor_id = $project->contractor_id;
                $task18->target_id = $work_volume->id; // WV id
                $task18->expired_at = $this->addHours(24);
                $task18->status = 18;
                $task18->prev_task_id = $prev_task->id ?? null;
                $task18->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: '.$task18->task_route();
                $notification->update([
                    'name' => 'Новая задача «'.$task18->name.'»',
                    'task_id' => $task18->id,
                    'user_id' => $task18->responsible_user_id,
                    'contractor_id' => $task18->contractor_id,
                    'project_id' => $task18->project_id,
                    'object_id' => $project->object_id,
                    'type' => 25,
                ]);
            }
        }

        DB::commit();

        return back();
    }

    public function delete_works($work_volume_id, $work_group_id)
    {
        $works = WorkVolumeWork::where('work_volume_works.work_volume_id', $work_volume_id)
            ->where('manual_works.work_group_id', $work_group_id)
            ->leftJoin('manual_works', 'manual_works.id', 'work_volume_works.manual_work_id')
            ->select('work_volume_works.*', 'manual_works.work_group_id')
            ->get();

        $works->each(function ($work) {
            $work->materials()->detach();
        });
        $works->each->delete();

        return redirect()->back();
    }

    public function stop_edit()
    {
        DB::beginTransaction();

        if (session()->has('edited_wv_id')) {
            $wv_id = session()->get('edited_wv_id');
            $time = session()->get('edit_start');

            $wv = WorkVolume::find($wv_id);
            $wv->depth = 0;
            $wv->save();

            $wv_mat = WorkVolumeMaterial::where('work_volume_id', $wv_id)->where('created_at', '>', $time);
            $wv_works = WorkVolumeWork::where('work_volume_id', $wv_id)->where('created_at', '>', $time);
            $wv_mat_works = WorkVolumeWorkMaterial::whereIn('wv_work_id', $wv_works->pluck('id'))->where('created_at', '>', $time);

            $wv_mat->delete();
            $wv_works->delete();
            $wv_mat_works->delete();

            session()->forget(['edited_wv_id', 'edited_wv_request_id', 'edit_start']);
        }

        DB::commit();

        return response()->json(true);
    }

    public function replace_material(Request $request)
    {
        DB::beginTransaction();
        $offer = CommercialOffer::find($request->commercial_offer_id);

        if ($offer) {
            if ($offer->commercial_offer_works()->count() > 0) {
                $offer_works = $offer->works();
            } else {
                $offer_works = WorkVolumeWork::query();

            }
        } else {
            $offer_works = WorkVolumeWork::query();
        }
        $works = $offer_works->whereIn('id', [$request->first_work_id, $request->second_work_id])->get();

        [$works->first()->order, $works->last()->order] = [$works->last()->order, $works->first()->order];

        $works->first()->save();
        $works->last()->save();

        DB::commit();

        return response()->json(true);
    }

    public function count_nodes(Request $request)
    {
        $weight = ManualNodeMaterials::where('node_id', $request->mat_id)->sum('count') * 1.05;

        if ($request->is_weight == 'false') {
            return response()->json(round($request->material_value / $weight, 3));
        } elseif ($request->is_weight == 'true') {
            return response()->json(round($weight * $request->material_value, 3));
        }
    }

    public function complect_materials(Request $request, $work_volume_id)
    {
        if (! $request->has('complect_ids')) {
            return back();
        }

        DB::beginTransaction();

        $complect_parts = WorkVolumeMaterial::find($request->complect_ids);
        $sum_weight = 0;
        if ($complect_parts->pluck('unit')->unique()->count() == 1 && $complect_parts->pluck('unit')->unique()->first() == 'шт') {
            foreach ($complect_parts as $material) {
                if ($material->manual->convertation_parameters()->where('unit', 'т')->first()) {
                    $sum_weight += $material->manual->convertation_parameters()->where('unit', 'т')->first()->value * $material->count;
                } else {
                    return back();
                }
            }
        } elseif ($complect_parts->pluck('unit')->unique()->count() == 1 && $complect_parts->pluck('unit')->unique()->first() == 'т') {
            foreach ($complect_parts as $material) {
                $sum_weight += $material->count;
            }
        } else {
            return back();
        }

        $complect = WorkVolumeMaterialComplect::create([
            'name' => $request->name,
            'wv_material_id' => $complect_parts->first(),
            'work_volume_id' => $work_volume_id,
        ]);

        $wv_complect = WorkVolumeMaterial::create([
            'user_id' => Auth::id(),
            'work_volume_id' => $work_volume_id,
            'manual_material_id' => $complect->id,
            'count' => $sum_weight,
            'is_our' => 1,
            'material_type' => 'complect',
            'unit' => 'т',
        ]);

        $complect_parts->each(function ($part) use ($wv_complect) {
            $part->complect_id = $wv_complect->id;
            $part->save();
        });

        DB::commit();

        return back();
    }

    public function detach_compile(Request $request, $work_volume_id)
    {
        WorkVolumeMaterial::find($request->complect_id)->destroy_complect();

        return response()->json(true);
    }

    public function close_work_volume(Request $request, $work_volume_id)
    {
        DB::beginTransaction();
        $work_volume = WorkVolume::findOrFail($work_volume_id);

        $work_volume->decline();

        DB::commit();

        return redirect(route('tasks::index'));
    }
}
