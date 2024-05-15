<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommercialOffer\AddSubcontractorRequest;
use App\Http\Requests\ProjectRequest\CommercialOfferReqRequest;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\CommercialOffer\CommercialOfferAdvancement;
use App\Models\CommercialOffer\CommercialOfferManualNote;
use App\Models\CommercialOffer\CommercialOfferManualRequirement;
use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\CommercialOffer\CommercialOfferNote;
use App\Models\CommercialOffer\CommercialOfferRequest;
use App\Models\CommercialOffer\CommercialOfferRequestFile;
use App\Models\CommercialOffer\CommercialOfferRequirement;
use App\Models\CommercialOffer\CommercialOfferWork;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;
use App\Models\Contractors\ContractorFile;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Manual\ManualWork;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectObject;
use App\Models\ProjectResponsibleUser;
use App\Models\Review;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeWork;
use App\Models\WorkVolume\WorkVolumeWorkMaterial;
use App\Notifications\CommercialOffer\AppointmentOfResponsibleForOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfOfferPileDrivingTaskNotice;
use App\Notifications\CommercialOffer\CustomerApprovalOfOfferSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\OfferCreationPilingDirectionTaskNotice;
use App\Notifications\CommercialOffer\OfferCreationSheetPilingTaskNotice;
use App\Notifications\CommercialOffer\OfferProcessingNotice;
use App\Services\Commerce\SplitService;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ProjectCommercialOfferController extends Controller
{
    use TimeCalculator;

    protected $prepareNotifications = [];

    public function card_tongue($project_id, $com_offer_id)
    {
        $commercial_offer = CommercialOffer::with('notes', 'requirements', 'advancements', 'project')
            ->findOrFail($com_offer_id);

        $work_volume = WorkVolume::with('works_offer.materials', 'works_offer.manual')->where('work_volumes.id', $commercial_offer->work_volume_id)
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name')
            ->first();

        $works = $commercial_offer->works->where('is_hidden', 0);

        $split_wv_mat = collect([]);

        $materials_ids = [];
        foreach ($works as $work) {
            $materials_ids[] = $work->relations->pluck('wv_material_id')->toArray();
        }

        $work_volume_materials = $work_volume->shown_materials;

        $commercial_offer_requests = CommercialOfferRequest::where('project_id', $project_id)
            ->where('commercial_offer_requests.commercial_offer_id', $commercial_offer->id)
            ->leftJoin('users', 'users.id', '=', 'commercial_offer_requests.user_id')
            ->select('commercial_offer_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $splits = $commercial_offer->mat_splits()->with(['subcontractor_file', 'buyback', 'security'])->get();

        //control splits material type
        foreach ($splits as $split) {
            $wv_mat = $work_volume->materials->where('manual_material_id', $split->man_mat_id)->first();
            if ($wv_mat) {
                if ($split->material_type != $wv_mat->material_type) {
                    $split->material_type = $wv_mat->material_type;
                    $split->save();
                }
            }
        }

        $works_files = $commercial_offer->works->where('subcontractor_file_id', '!=', null)->pluck('subcontractor_file_id')->unique();
        $materials_files = $splits->pluck('subcontractor_file')->unique();

        $subcontractors = Contractor::with(['file' => function ($q) use ($works_files, $commercial_offer) {
            $q->where('type', 0)->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($works_files, $commercial_offer) {
            $q->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $material_subcontractors = Contractor::with(['file' => function ($q) use ($materials_files, $commercial_offer) {
            $q->where('type', 1)->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($materials_files, $commercial_offer) {
            $q->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $resp_con = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 2)->first();

        $work_volumes = WorkVolume::where('project_id', $project_id)->where('type', '!=', 2)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        $agree_task = Task::where('project_id', $project_id)->where('status', 16)->where('target_id', $com_offer_id)->where('is_solved', 0)->where('responsible_user_id', Auth::id())->first();

        $com_offers_options = CommercialOffer::where('project_id', $project_id)
            ->with('work_volume')
            ->whereHas('work_volume', function ($q) {
                $q->where('status', 2);
            })
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->orderBy('version', 'asc')
            ->groupBy('is_tongue', 'option')
            ->select('commercial_offers.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        return view('projects.commercial_offer.card', [
            'commercial_offer' => $commercial_offer,
            'work_volumes' => $work_volumes->get(),
            'works' => $works,
            'work_volume' => $work_volume,
            'split_wv_mat' => $split_wv_mat,
            'commercial_offer_requests' => $commercial_offer_requests->get(),
            'work_volume_materials' => $work_volume_materials->unique('manual_material_id')->sortBy('work_group_id'),
            'subcontractors' => $subcontractors,
            'material_subcontractors' => $material_subcontractors,
            'resp' => $resp_con,
            'is_tongue' => 1,
            'splits' => $splits,
            'com_offers_options' => $com_offers_options,
            'agree_task' => $agree_task,
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function card_pile($project_id, $com_offer_id)
    {
        $commercial_offer = CommercialOffer::with('notes', 'requirements', 'advancements', 'project')
            ->findOrFail($com_offer_id);

        $work_volume = WorkVolume::where('work_volumes.id', $commercial_offer->work_volume_id)
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name')
            ->first();

        $works = $commercial_offer->works->where('is_hidden', 0);
        $split_wv_mat = $commercial_offer->adapted_splits;

        $materials_ids = [];
        foreach ($works as $work) {
            $materials_ids[] = $work->relations->pluck('wv_material_id')->toArray();
        }

        $work_volume_materials = $work_volume->shown_materials;

        $commercial_offer_requests = CommercialOfferRequest::where('project_id', $project_id)
            ->where('commercial_offer_requests.commercial_offer_id', $commercial_offer->id)
            ->leftJoin('users', 'users.id', '=', 'commercial_offer_requests.user_id')
            ->select('commercial_offer_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $splits = CommercialOfferMaterialSplit::with('WV_material.manual.category')->where('commercial_offer_material_splits.com_offer_id', $com_offer_id)
            ->leftjoin('contractor_files', 'contractor_files.id', '=', 'commercial_offer_material_splits.subcontractor_file_id')
            ->leftjoin('contractors', 'contractors.id', '=', 'contractor_files.contractor_id')
            ->select('commercial_offer_material_splits.*', 'contractors.short_name')
            ->get();

        $works_files = $commercial_offer->works->where('subcontractor_file_id', '!=', null)->pluck('subcontractor_file_id')->unique();
        $materials_files = $splits->where('subcontractor_file_id', '!=', null)->pluck('subcontractor_file_id')->unique();

        $subcontractors = Contractor::with(['file' => function ($q) use ($works_files, $commercial_offer) {
            $q->where('type', 0)->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($works_files, $commercial_offer) {
            $q->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $material_subcontractors = Contractor::with(['file' => function ($q) use ($materials_files, $commercial_offer) {
            $q->where('type', 1)->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($materials_files, $commercial_offer) {
            $q->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $resp_con = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 1)->first();

        $work_volumes = WorkVolume::where('project_id', $project_id)->where('type', '!=', 2)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        $agree_task = Task::where('project_id', $project_id)->where('status', 16)->where('target_id', $com_offer_id)->where('is_solved', 0)->where('responsible_user_id', Auth::id())->first();

        $com_offers_options = CommercialOffer::where('project_id', $project_id)
            ->with('work_volume')
            ->whereHas('work_volume', function ($q) {
                $q->where('status', 2);
            })
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->orderBy('version', 'asc')
            ->groupBy('is_tongue', 'option')
            ->select('commercial_offers.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        return view('projects.commercial_offer.card', [
            'commercial_offer' => $commercial_offer,
            'work_volumes' => $work_volumes->get(),
            'works' => $works,
            'split_wv_mat' => $split_wv_mat,
            'work_volume' => $work_volume,
            'commercial_offer_requests' => $commercial_offer_requests->get(),
            'work_volume_materials' => $work_volume_materials->unique('manual_material_id')->sortBy('work_group_id'),
            'subcontractors' => $subcontractors,
            'material_subcontractors' => $material_subcontractors,
            'resp' => $resp_con,
            'is_tongue' => 0,
            'splits' => $splits,
            'com_offers_options' => $com_offers_options,
            'agree_task' => $agree_task,
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function card_double($project_id, $com_offer_id)
    {
        $commercial_offer = CommercialOffer::with('notes', 'requirements', 'advancements', 'project')
            ->findOrFail($com_offer_id);

        // if ($commercial_offer->status != 1) {
        //     abort(403);
        // }

        $manual_notes = CommercialOfferManualNote::where('commercial_offer_type', '=', $commercial_offer->is_tongue ? 1 : 2)->get();
        $manual_requirements = CommercialOfferManualRequirement::where('commercial_offer_type', '=', $commercial_offer->is_tongue ? 1 : 2)->get();

        $work_volume = WorkVolume::where('work_volumes.id', $commercial_offer->work_volume_id)
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name')
            ->first();

        $works = $commercial_offer->works->where('is_hidden', 0);
        $split_wv_mat = $commercial_offer->adapted_splits;

        $materials_ids = [];
        foreach ($works as $work) {
            $materials_ids[] = $work->relations->pluck('wv_material_id')->toArray();
        }

        $work_volume_materials = $work_volume->shown_materials;

        $commercial_offer_requests = CommercialOfferRequest::where('project_id', $project_id)
            ->where('commercial_offer_requests.commercial_offer_id', $commercial_offer->id)
            ->leftJoin('users', 'users.id', '=', 'commercial_offer_requests.user_id')
            ->select('commercial_offer_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $splits = CommercialOfferMaterialSplit::with('WV_material.manual.category')->where('commercial_offer_material_splits.com_offer_id', $com_offer_id)
            ->leftjoin('contractor_files', 'contractor_files.id', '=', 'commercial_offer_material_splits.subcontractor_file_id')
            ->leftjoin('contractors', 'contractors.id', '=', 'contractor_files.contractor_id')
            ->select('commercial_offer_material_splits.*', 'contractors.short_name')
            ->get();

        $works_files = $commercial_offer->works->where('subcontractor_file_id', '!=', null)->pluck('subcontractor_file_id')->unique();
        $materials_files = $splits->where('subcontractor_file_id', '!=', null)->pluck('subcontractor_file_id')->unique();

        $subcontractors = Contractor::with(['file' => function ($q) use ($works_files, $commercial_offer) {
            $q->where('type', 0)->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($works_files, $commercial_offer) {
            $q->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $material_subcontractors = Contractor::with(['file' => function ($q) use ($materials_files, $commercial_offer) {
            $q->where('type', 1)->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($materials_files, $commercial_offer) {
            $q->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $resp_con = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 2)->first();

        $work_volumes = WorkVolume::where('project_id', $project_id)->where('type', '!=', 2)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        $com_offers_options = CommercialOffer::where('project_id', $project_id)
            ->with('work_volume')
            ->whereHas('work_volume', function ($q) {
                $q->where('status', 2);
            })
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->orderBy('version', 'asc')
            ->groupBy('is_tongue', 'option')
            ->select('commercial_offers.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        return view('projects.commercial_offer.card', [
            'commercial_offer' => $commercial_offer,
            'works' => $works,
            'split_wv_mat' => $split_wv_mat,
            'work_volume' => $work_volume,
            'commercial_offer_requests' => $commercial_offer_requests->get(),
            'work_volume_materials' => $work_volume_materials->unique('manual_material_id')->sortBy('work_group_id'),
            'subcontractors' => $subcontractors,
            'material_subcontractors' => $material_subcontractors,
            'resp' => $resp_con,
            'is_tongue' => 1,
            'splits' => $splits,
            'com_offers_options' => $com_offers_options,
            'agree_task' => false,
            'work_volumes' => $work_volumes,
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function edit($project_id, $com_offer_id)
    {
        $commercial_offer = CommercialOffer::with('notes', 'requirements', 'advancements', 'project')
            ->findOrFail($com_offer_id);

        if ($commercial_offer->status != 1) {
            //there is a bug when task is not closed, but CO was already finished
            if (strpos(url()->previous(), 'common_task')) {
                $commercial_offer->unsolved_tasks->where('status', 5)->each(function ($task) {
                    $task->solve();
                });
            }

            abort(403);
        } elseif ($commercial_offer->is_uploaded) {
            return redirect()->route('projects::commercial_offer::card_'.($commercial_offer->is_tongue ? 'tongue' : 'pile'), [$project_id, $com_offer_id]);
        }

        $manual_notes = CommercialOfferManualNote::where('commercial_offer_type', '=', $commercial_offer->is_tongue ? 1 : 2)->get();
        $manual_requirements = CommercialOfferManualRequirement::where('commercial_offer_type', '=', $commercial_offer->is_tongue ? 1 : 2)->get();

        $work_volume = WorkVolume::where('work_volumes.id', $commercial_offer->work_volume_id)
            ->with('works_offer.materials', 'works_offer.manual')
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name')
            ->first();

        $split_wv_mat = collect([]);

        $materials_ids = [];
        foreach ($commercial_offer->works as $work) {
            $materials_ids[] = $work->relations->pluck('wv_material_id')->toArray();
        }

        $work_volume_materials = $work_volume->shown_materials;

        $commercial_offer_requests = CommercialOfferRequest::where('project_id', $project_id)
            ->where('commercial_offer_requests.commercial_offer_id', $commercial_offer->id)
            ->leftJoin('users', 'users.id', '=', 'commercial_offer_requests.user_id')
            ->select('commercial_offer_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $splits = $commercial_offer->mat_splits()->with(['subcontractor_file', 'buyback', 'security'])->get();
        //delete children without parents
        foreach ($splits->where('parent_id', '!=', null) as $child) {
            if ($child->parent->com_offer_id != $child->com_offer_id) {
                $child->delete();
            }
        }

        $works_files = $commercial_offer->works->pluck('subcontractor_file_id')->unique();
        $materials_files = $splits->pluck('subcontractor_file')->unique();

        $subcontractors = Contractor::with(['file' => function ($q) use ($works_files, $commercial_offer) {
            $q->where('type', 0)->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($works_files, $commercial_offer) {
            $q->whereIn('id', $works_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $material_subcontractors = Contractor::with(['file' => function ($q) use ($materials_files, $commercial_offer) {
            $q->where('type', 1)->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        }])->whereHas('file', function ($q) use ($materials_files, $commercial_offer) {
            $q->whereIn('id', $materials_files)->whereIn('commercial_offer_id', $commercial_offer->siblings->pluck('id'));
        })->get();

        $signers = collect([]);
        $signers_groups = Group::whereIn('id', [5/*3*/, 8/*5*/, 50/*7*/, 6/*24*/, 19/*33*/]);
        foreach ($signers_groups as $group) {
            $signers = $signers->merge($group->getUsers());
        }

        $project = Project::findOrFail($project_id);
        $object = ProjectObject::findOrFail($project->object_id);
        $title = isset($commercial_offer->title) ? $commercial_offer->title : 'Коммерческое предложение на '.$work_volume->project_name.' «'.$object->name.'» по адресу: '.$object->address.
            (isset($object->cadastral_number) ? ', на земельном участке с кадастровым номером '.$object->cadastral_number : '');
        $contacts = ContractorContact::where('contractor_id', $project->contractor_id)->get();

        return view('projects.commercial_offer.edit', [
            'commercial_offer' => $commercial_offer,
            'work_volume' => $work_volume,
            'commercial_offer_requests' => $commercial_offer_requests->get(),
            'work_volume_materials' => $work_volume_materials->unique('manual_material_id')->sortBy('work_group_id'),
            'split_wv_mat' => $split_wv_mat,
            'subcontractors' => $subcontractors,
            'material_subcontractors' => $material_subcontractors,
            'manual_notes' => $manual_notes,
            'manual_requirements' => $manual_requirements,
            'signers' => $signers,
            'splits' => $splits,
            'contacts' => $contacts,
            'title' => trim($title),
            'work_groups' => (new ManualWork())->work_group,
        ]);
    }

    public function split_material(Request $request, $com_offer_id)
    {
        DB::beginTransaction();
        $old_split = CommercialOfferMaterialSplit::find($request->split_id);
        $splitService = new SplitService();

        if ($request->has('target_split_id')) {
            $target_split = CommercialOfferMaterialSplit::find($request->target_split_id);
            $splitService->mergeRelatedSplitWithOldOne($old_split, $request->count, $target_split);
        } else {
            $splitService->splitMore($old_split, $request->count, $request->new_type, $request->time, $request->comment);
        }

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function set_contract_number(Request $request, $project_id, $com_offer_id)
    {
        DB::beginTransaction();

        if (in_array($request->field, ['contract_number', 'contract_date'])) {
            CommercialOffer::where('id', $com_offer_id)->update([$request->field => $request->value]);
        }

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function add_advancement(Request $request, $project_id, $com_offer_id)
    {
        $com_offer = CommercialOffer::findOrFail($com_offer_id);
        DB::beginTransaction();

        $avans_value = number_format(($request->avans_unit === '%' ? $request->avans_value / 100 * $request->max_avans : $request->avans_value), 2, ',', ' ');
        CommercialOfferAdvancement::create(
            [
                'commercial_offer_id' => $com_offer_id,
                'value' => $request->avans_value ? $request->avans_value : 0,
                'is_percent' => $request->avans_unit === '%' ? 1 : 0,
                'description' => trim(($request->avans_title.' в размере '.$avans_value.' руб.'.($request->avans_note ? ', '.$request->avans_note : ''))),
            ]
        );

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function change_advancement(Request $request, $com_offer_id)
    {
        $count = CommercialOfferAdvancement::where('id', $request->adv_id)->update(['description' => trim($request->adv_desc)]);

        if (trim($request->adv_desc) == '') {
            CommercialOfferAdvancement::where('id', $request->adv_id)->delete();
        } elseif ($count == 0) {
            CommercialOfferAdvancement::create(
                [
                    'commercial_offer_id' => $com_offer_id,
                    'value' => 0,
                    'is_percent' => 0,
                    'description' => trim($request->adv_desc),
                ]
            );
        }

        return \GuzzleHttp\json_encode(0);
    }

    public function change_comment(Request $request, $com_offer_id)
    {
        if ($request->note_id > 0) {
            $count = CommercialOfferNote::where('id', $request->note_id)->update(['note' => trim($request->note)]);

            if (trim($request->note) == '') {
                CommercialOfferNote::where('id', $request->note_id)->delete();

                return \GuzzleHttp\json_encode(-1);
            } elseif ($count == 0) {
                $new_note = new CommercialOfferNote([
                    'commercial_offer_id' => $com_offer_id,
                    'note' => trim($request->note),
                ]);
                $new_note->save();

                return \GuzzleHttp\json_encode($new_note->id);
            }

            return \GuzzleHttp\json_encode(0);

        } else {
            $new_note = new CommercialOfferNote([
                'commercial_offer_id' => $com_offer_id,
                'note' => trim($request->note),
            ]);
            $new_note->save();

            return \GuzzleHttp\json_encode($new_note->id);
        }
    }

    public function change_require(Request $request, $com_offer_id)
    {
        if ($request->req_id > 0) {
            $count = CommercialOfferRequirement::where('id', $request->req_id)->update(['requirement' => trim($request->req)]);

            if (trim($request->req) == '') {
                CommercialOfferRequirement::where('id', $request->req_id)->delete();

                return \GuzzleHttp\json_encode(-1);
            } elseif ($count == 0) {
                $new_req = new CommercialOfferRequirement([
                    'commercial_offer_id' => $com_offer_id,
                    'requirement' => trim($request->req),
                ]);
                $new_req->save();

                return \GuzzleHttp\json_encode($new_req->id);
            }

            return \GuzzleHttp\json_encode(0);
        } else {
            $new_req = new CommercialOfferRequirement([
                'commercial_offer_id' => $com_offer_id,
                'requirement' => trim($request->req),
            ]);
            $new_req->save();

            return \GuzzleHttp\json_encode($new_req->id);
        }
    }

    public function delete_require(Request $request)
    {
        CommercialOfferRequirement::where('id', $request->id)->delete();

        return \GuzzleHttp\json_encode(0);
    }

    public function delete_advancement(Request $request)
    {
        CommercialOfferAdvancement::where('id', $request->id)->delete();

        return \GuzzleHttp\json_encode(0);
    }

    public function delete_comment(Request $request)
    {
        CommercialOfferNote::where('id', $request->id)->delete();

        return \GuzzleHttp\json_encode(0);
    }

    public function request_store(Request $request, $project_id)
    {
        DB::beginTransaction();

        $offer_id = $request->add_tongue == 1 ? $request->tongue_offer_id : $request->pile_offer_id;
        $project = Project::findOrFail($project_id);
        $offer = CommercialOffer::find($offer_id);
        $SOP = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 2)->first();

        if ($SOP) {
            if ($offer) {
                if (($offer->is_tongue == 1) or ($offer->is_tongue == 1 && $offer->status != 1)) {
                    $offer->decline();

                    $commercial_offer = new CommercialOffer();
                    $commercial_offer->name = 'Коммерческое предложение (шпунтовое направление)';
                    $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 2)->first()->user_id;
                    $commercial_offer->project_id = $project->id;
                    $commercial_offer->work_volume_id = WorkVolume::where('project_id', $project->id)->where('status', 2)->where('type', 0)->where('option', $offer->option)->first()->id;
                    $commercial_offer->status = 1;
                    $commercial_offer->version = ($offer->version ?? 0) + 1;
                    $commercial_offer->option = $offer->option;
                    $commercial_offer->file_name = 0;
                    $commercial_offer->is_tongue = 1;

                    $commercial_offer->save();

                    if ($offer) {
                        foreach ($offer->notes as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        foreach ($offer->requirements as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        foreach ($offer->advancements as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        $splits = CommercialOfferMaterialSplit::where('com_offer_id', $offer->id)->get();

                        $remember_old_new_split = []; //replication main split types
                        foreach ($splits->where('parent_id', null) as $mat_split_old) {
                            $mat_split_copy = $mat_split_old->replicate();
                            $mat_split_copy->com_offer_id = $commercial_offer->id;
                            $mat_split_copy->man_mat_id = $mat_split_old->man_mat_id;

                            $subcontractor_file = $mat_split_old->subcontractor_file;
                            if ($subcontractor_file) {
                                $file_copy = $subcontractor_file->replicate();
                                $file_copy->commercial_offer_id = $commercial_offer->id;
                                $file_copy->save();

                                $mat_split_copy->subcontractor_file_id = $file_copy->id;
                            }
                            $mat_split_copy->save();

                            $remember_old_new_split[$mat_split_old->id] = $mat_split_copy->id;
                        }
                        // replicating children and updating parent_id
                        foreach ($splits->where('parent_id', '!=', null) as $mat_split_old) {
                            $mat_split_copy = $mat_split_old->replicate();
                            $mat_split_copy->com_offer_id = $commercial_offer->id;
                            $mat_split_copy->man_mat_id = $mat_split_old->man_mat_id;

                            if (! isset($remember_old_new_split[$mat_split_old->parent_id])) {
                                continue;
                            }
                            $mat_split_copy->parent_id = $remember_old_new_split[$mat_split_old->parent_id];
                            $subcontractor_file = $mat_split_old->subcontractor_file;
                            if ($subcontractor_file) {
                                $file_copy = $subcontractor_file->replicate();
                                $file_copy->commercial_offer_id = $commercial_offer->id;
                                $file_copy->save();

                                $mat_split_copy->subcontractor_file_id = $file_copy->id;
                            }
                            $mat_split_copy->save();
                        }

                        if ($offer->commercial_offer_works()->count()) {
                            foreach ($offer->works as $work) {
                                $new_work = $work->replicate();
                                $new_work->commercial_offer_id = $commercial_offer->id;
                                $new_work->save();
                            }
                        } else {
                            foreach ($commercial_offer->work_volume->works as $work) {
                                CommercialOfferWork::create([
                                    'work_volume_work_id' => $work->id,
                                    'commercial_offer_id' => $commercial_offer->id,
                                    'count' => $work->count,
                                    'term' => $work->term,
                                    'price_per_one' => $work->price_per_one,
                                    'result_price' => $work->result_price,
                                    'subcontractor_file_id' => $work->subcontractor_file_id,
                                    'is_hidden' => $work->is_hidden,
                                    'order' => $work->order,
                                ]);
                            }
                        }
                    }

                    $task = new Task([
                        'project_id' => $project_id,
                        'name' => 'Формирование КП (шпунтовое направление)',
                        'responsible_user_id' => ProjectResponsibleUser::where('project_id', $project->id)->where('role', 2)->first()->user_id,
                        'contractor_id' => $project->contractor_id,
                        'target_id' => $commercial_offer->id,
                        'expired_at' => Carbon::now()->addHours(24),
                        'status' => 5,
                    ]);

                    $task->save();

                    OfferCreationSheetPilingTaskNotice::send(
                        $task->responsible_user_id,
                        [
                            'name' => 'Новая задача «'.$task->name.'»',
                            'additional_info' => ' Ссылка на задачу: ',
                            'url' => $task->task_route(),
                            'task_id' => $task->id,
                            'contractor_id' => $project->contractor_id,
                            'project_id' => $project->id,
                            'object_id' => $project->object_id,
                        ]
                    );
                } else {
                    $commercial_offer = $offer;
                }
            }
        } else {
            if (isset($offer) && $offer->is_tongue == 1) {
                $thisTask = Task::where('project_id', $project_id)->where('status', 15)->where('is_solved', 0)->count();
                $task = Task::where('project_id', $project->id)->where('status', 4)->where('target_id', $offer_id)->where('is_solved', 0)->first();

                if ($thisTask == 0) {
                    $tongueTask = new Task();

                    $tongueTask->project_id = $project_id;
                    $tongueTask->name = 'Назначение ответственного за КП (шпунт)';
                    $tongueTask->status = 15;
                    $tongueTask->responsible_user_id = Group::find(50/*7*/)->getUsers()->first()->id;
                    $tongueTask->contractor_id = $project->contractor_id;
                    $tongueTask->expired_at = Carbon::now()->addHours(3);
                    $tongueTask->target_id = 0;
                    $tongueTask->prev_task_id = $task->id ?? 0;

                    $tongueTask->save();

                    AppointmentOfResponsibleForOfferSheetPilingTaskNotice::send(
                        $tongueTask->responsible_user_id,
                        [
                            'name' => 'Новая задача «'.$tongueTask->name.'»',
                            'additional_info' => ' Ссылка на задачу: ',
                            'url' => $tongueTask->task_route(),
                            'task_id' => $tongueTask->id,
                            'contractor_id' => $tongueTask->project_id ? Project::find($tongueTask->project_id)->contractor_id : null,
                            'project_id' => $tongueTask->project_id ? $tongueTask->project_id : null,
                            'object_id' => $tongueTask->project_id ? Project::find($tongueTask->project_id)->object_id : null,
                        ]
                    );
                }
            }
        }

        if (isset($commercial_offer) && $commercial_offer->is_tongue == 1) {
            $com_offer_request = new CommercialOfferRequest();
            $com_offer_request->user_id = Auth::id();
            $com_offer_request->project_id = $project->id;
            $com_offer_request->commercial_offer_id = $commercial_offer->id;
            $com_offer_request->status = 0;
            $com_offer_request->description = $request->descriptionTongue ? $request->descriptionTongue : $request->description;
            $com_offer_request->is_tongue = 1;

            $com_offer_request->save();

            if ($request->tongue_documents) {
                foreach ($request->tongue_documents as $document) {
                    $file = new CommercialOfferRequestFile();

                    $mime = $document->getClientOriginalExtension();
                    $file_name = 'project-'.$com_offer_request->project_id.'-com_offer'.$com_offer_request->commercial_offer_id.'-request_file-'.uniqid().'.'.$mime;

                    Storage::disk('commercial_offer_request_files')->put($file_name, File::get($document));

                    FileEntry::create([
                        'filename' => $file_name,
                        'size' => $document->getSize(),
                        'mime' => $document->getClientMimeType(),
                        'original_filename' => $document->getClientOriginalName(),
                        'user_id' => Auth::user()->id,
                    ]);

                    $file->file_name = $file_name;
                    $file->request_id = $com_offer_request->id;
                    $file->is_result = 0;
                    $file->original_name = $document->getClientOriginalName();

                    $file->save();
                }
            }

            if ($request->project_documents_tongue) {
                $project_docs = ProjectDocument::whereIn('id', $request->project_documents_tongue)->get();

                foreach ($request->project_documents_tongue as $document_id) {
                    $file = new CommercialOfferRequestFile();

                    $file->file_name = $project_docs->where('id', $document_id)->first()->file_name;
                    $file->request_id = $com_offer_request->id;
                    $file->is_result = 0;
                    $file->original_name = $project_docs->where('id', $document_id)->first()->name;
                    $file->is_proj_doc = 1;

                    $file->save();
                }
            }
        }

        if ($offer) {
            if (($offer->is_tongue == 0) || ($offer->is_tongue == 0 && $offer->status != 1)) {
                $offer->decline();

                $commercial_offer = new CommercialOffer();

                $commercial_offer->name = 'Коммерческое предложение (свайное направление)';
                $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 1)->first()->user_id;
                $commercial_offer->project_id = $project->id;
                $commercial_offer->work_volume_id = WorkVolume::where('project_id', $project->id)->where('status', 2)->where('option', $offer->option)->where('type', 1)->first()->id;
                $commercial_offer->status = 1;
                $commercial_offer->version = ($offer->version ?? 0) + 1;
                $commercial_offer->option = $offer->option;
                $commercial_offer->file_name = 0;
                $commercial_offer->is_tongue = 0;

                $commercial_offer->save();

                if ($offer) {
                    foreach ($offer->notes as $item) {
                        $new_note = $item->replicate();
                        $new_note->commercial_offer_id = $commercial_offer->id;
                        $new_note->save();
                    }

                    foreach ($offer->requirements as $item) {
                        $new_note = $item->replicate();
                        $new_note->commercial_offer_id = $commercial_offer->id;
                        $new_note->save();
                    }

                    foreach ($offer->advancements as $item) {
                        $new_note = $item->replicate();
                        $new_note->commercial_offer_id = $commercial_offer->id;
                        $new_note->save();
                    }

                    $splits = CommercialOfferMaterialSplit::where('com_offer_id', $offer->id)->get();

                    $remember_old_new_split = []; //replication main split types
                    foreach ($splits->where('parent_id', null) as $mat_split_old) {
                        $mat_split_copy = $mat_split_old->replicate();
                        $mat_split_copy->com_offer_id = $commercial_offer->id;
                        $mat_split_copy->man_mat_id = $mat_split_old->man_mat_id;

                        $subcontractor_file = $mat_split_old->subcontractor_file;
                        if ($subcontractor_file) {
                            $file_copy = $subcontractor_file->replicate();
                            $file_copy->commercial_offer_id = $commercial_offer->id;
                            $file_copy->save();

                            $mat_split_copy->subcontractor_file_id = $file_copy->id;
                        }
                        $mat_split_copy->save();

                        $remember_old_new_split[$mat_split_old->id] = $mat_split_copy->id;
                    }
                    // replicating children and updating parent_id
                    foreach ($splits->where('parent_id', '!=', null) as $mat_split_old) {
                        $mat_split_copy = $mat_split_old->replicate();
                        $mat_split_copy->com_offer_id = $commercial_offer->id;
                        $mat_split_copy->man_mat_id = $mat_split_old->man_mat_id;

                        if (! isset($remember_old_new_split[$mat_split_old->parent_id])) {
                            continue;
                        }
                        $mat_split_copy->parent_id = $remember_old_new_split[$mat_split_old->parent_id];
                        $subcontractor_file = $mat_split_old->subcontractor_file;
                        if ($subcontractor_file) {
                            $file_copy = $subcontractor_file->replicate();
                            $file_copy->commercial_offer_id = $commercial_offer->id;
                            $file_copy->save();

                            $mat_split_copy->subcontractor_file_id = $file_copy->id;
                        }
                        $mat_split_copy->save();
                    }

                    if ($offer->commercial_offer_works()->count()) {
                        foreach ($offer->works as $work) {
                            $new_work = $work->replicate();
                            $new_work->commercial_offer_id = $commercial_offer->id;
                            $new_work->save();
                        }
                    } else {
                        foreach ($commercial_offer->work_volume->works as $work) {
                            CommercialOfferWork::create([
                                'work_volume_work_id' => $work->id,
                                'commercial_offer_id' => $commercial_offer->id,
                                'count' => $work->count,
                                'term' => $work->term,
                                'price_per_one' => $work->price_per_one,
                                'result_price' => $work->result_price,
                                'subcontractor_file_id' => $work->subcontractor_file_id,
                                'is_hidden' => $work->is_hidden,
                                'order' => $work->order,
                            ]);
                        }
                    }
                }

                $task = new Task([
                    'project_id' => $project_id,
                    'name' => 'Формирование КП (свайное направление)',
                    'responsible_user_id' => ProjectResponsibleUser::where('project_id', $commercial_offer->project_id)->where('role', 1)->first()->user_id,
                    'contractor_id' => $project->contractor_id,
                    'target_id' => $commercial_offer->id,
                    'expired_at' => Carbon::now()->addHours(24),
                    'status' => 5,
                ]);

                $task->save();

                OfferCreationPilingDirectionTaskNotice::send(
                    $task->responsible_user_id,
                    [
                        'name' => 'Новая задача «'.$task->name.'»',
                        'additional_info' => ' Ссылка на задачу: ',
                        'url' => $task->task_route(),
                        'task_id' => $task->id,
                        'contractor_id' => $project->contractor_id,
                        'project_id' => $project->id,
                        'object_id' => $project->object_id,
                    ]
                );
            } else {
                $commercial_offer = $offer;
            }
        }

        if ($commercial_offer->is_tongue == 0) {
            $com_offer_request = new CommercialOfferRequest();
            $com_offer_request->user_id = Auth::id();
            $com_offer_request->project_id = $project->id;
            $com_offer_request->commercial_offer_id = $commercial_offer->id;
            $com_offer_request->status = 0;
            $com_offer_request->description = $request->descriptionPile ? $request->descriptionPile : $request->description;
            $com_offer_request->is_tongue = 0;

            $com_offer_request->save();

            if ($request->pile_documents) {
                foreach ($request->pile_documents as $document) {
                    $file = new CommercialOfferRequestFile();

                    $mime = $document->getClientOriginalExtension();
                    $file_name = 'project-'.$com_offer_request->project_id.'-com_offer'.$com_offer_request->commercial_offer_id.'-request_file-'.uniqid().'.'.$mime;

                    Storage::disk('commercial_offer_request_files')->put($file_name, File::get($document));

                    FileEntry::create([
                        'filename' => $file_name,
                        'size' => $document->getSize(),
                        'mime' => $document->getClientMimeType(),
                        'original_filename' => $document->getClientOriginalName(),
                        'user_id' => Auth::user()->id,
                    ]);

                    $file->file_name = $file_name;
                    $file->request_id = $com_offer_request->id;
                    $file->is_result = 0;
                    $file->original_name = $document->getClientOriginalName();

                    $file->save();
                }
            }

            if ($request->project_documents_pile) {
                $project_docs = ProjectDocument::whereIn('id', $request->project_documents_pile)->get();

                foreach ($request->project_documents_pile as $document_id) {
                    $file = new CommercialOfferRequestFile();

                    $file->file_name = $project_docs->where('id', $document_id)->first()->file_name;
                    $file->request_id = $com_offer_request->id;
                    $file->is_result = 0;
                    $file->original_name = $project_docs->where('id', $document_id)->first()->name;
                    $file->is_proj_doc = 1;

                    $file->save();
                }
            }
        }

        if (isset($CO) and (($request->has('add_tongue') && ! $request->has('add_pile')) or ($request->has('add_pile') && ! $request->has('add_tongue')))) {
            $type = $request->has('add_tongue') ? 0 : 1;
            $com_offer_for_update = CommercialOffer::where('project_id', $project_id)->where('is_tongue', $type)->get()->last();
            if (isset($com_offer_for_update)) {
                $is_tongue = $com_offer_for_update->is_tongue;
                // 'revive' CO
                $com_offer_for_update->update(['status' => 5]);
                // 'revive' client agreement task
                $task = Task::where('project_id', $project->id)->where('status', 6)->where('target_id', $com_offer_for_update->id)->first();
                $task->update(['is_solved' => 0]);

                DB::commit();

                $notificationClass = $is_tongue ?
                    CustomerApprovalOfOfferSheetPilingTaskNotice::class :
                    CustomerApprovalOfOfferPileDrivingTaskNotice::class;
                $notificationClass::send(
                    $task->responsible_user_id,
                    [
                        'name' => 'Новая задача «'.$task->name.'»',
                        'additional_info' => ' Ссылка на задачу: ',
                        'url' => $task->task_route(),
                        'task_id' => $task->id,
                        'contractor_id' => $task->contractor_id,
                        'project_id' => $task->project_id,
                        'object_id' => $project->object_id,
                    ]
                );
            } else {
                DB::commit();
            }
        }

        //        DB::commit();

        return back()->with('com_offer', true);
    }

    public function request_update(Request $request)
    {
        DB::beginTransaction();

        $com_offer_request = CommercialOfferRequest::findOrFail($request->offer_request_id);

        if (isset($request->status)) {
            $com_offer_request->status = $request->status == 'confirm' ? 1 : 2;
            $com_offer_request->result_comment = $request->result_comment;
        }

        if ($request->documents) {
            foreach ($request->documents as $document) {
                $file = new CommercialOfferRequestFile();

                $mime = $document->getClientOriginalExtension();
                $file_name = 'project-'.$com_offer_request->project_id.'-com_offer'.$com_offer_request->commercial_offer_id.'-request_file-'.uniqid().'.'.$mime;

                Storage::disk('commercial_offer_request_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->request_id = $com_offer_request->id;
                $file->is_result = 1;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }

        if ($request->project_documents) {
            $project_docs = ProjectDocument::whereIn('id', $request->project_documents)->get();

            foreach ($request->project_documents as $document_id) {
                $file = new CommercialOfferRequestFile();

                $file->file_name = $project_docs->where('id', $document_id)->first()->file_name;
                $file->request_id = $com_offer_request->id;
                $file->is_result = 1;
                $file->original_name = $project_docs->where('id', $document_id)->first()->name;
                $file->is_proj_doc = 1;

                $file->save();
            }
        }

        $com_offer_request->save();

        $user = auth()->user()->long_full_name;
        $KP = CommercialOffer::find($com_offer_request->commercial_offer_id);
        $proj = Project::find($com_offer_request->project_id);

        DB::commit();

        OfferProcessingNotice::send(
            $com_offer_request->user_id,
            [
                'name' => ('Пользователь '.$user.' '.
                    ($request->status == 'confirm' ? 'подтвердил(а) ' : 'отклонил(а) ').'заявку на редактирование КП '.($com_offer_request->is_tongue ? 'шпунтового' : 'свайного')
                    .' направления версии '.$KP->version.' по проекту '.Project::find($com_offer_request->project_id)->name),
                'additional_info' => "\r\nЗаказчик: ".$proj->contractor_name.
                    "\r\nНазвание объекта: ".$proj->object->name.
                    "\r\nАдрес объекта: ".$proj->object->address.
                    "\r\n".'Ссылка на проект: ',
                'url' => route('projects::card', $com_offer_request->project_id),
                'contractor_id' => $proj->contractor_id,
                'project_id' => $com_offer_request->project_id,
                'object_id' => $proj->object_id,
                'target_id' => $request->offer_request_id,
                'status' => 4,
            ]
        );

        return back();
    }

    public function attach_subcontractor(AddSubcontractorRequest $request, $wv_id)
    {
        DB::beginTransaction();

        $commercial_offer = CommercialOffer::find($request->com_offer_id);
        if ($request->document) {
            $file = new ContractorFile();

            $mime = $request->document->getClientOriginalExtension();
            $file_name = 'project-'.CommercialOffer::find($request->com_offer_id)->project_id.'-com_offers'.$request->commercial_offer_id.'-contractor_file-'.uniqid().'.'.$mime;

            Storage::disk('commercial_offers_contractor_files')->put($file_name, File::get($request->document));

            FileEntry::create([
                'filename' => $file_name,
                'size' => $request->document->getSize(),
                'mime' => $request->document->getClientMimeType(),
                'original_filename' => $request->document->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);

            $file->file_name = $file_name;
            $file->commercial_offer_id = $request->com_offer_id;
            $file->type = $request->type;
            $file->contractor_id = $request->subcontractor_id;
            $file->original_name = $request->document->getClientOriginalName();

            $file->save();

            if ($request->type === '1') {
                CommercialOfferMaterialSplit::whereIn('id', $request->subcontractor_works)->update(['subcontractor_file_id' => $file->id]);
            } else {
                $commercial_offer->worksForToggling()->whereIn('id', $request->subcontractor_works)->update(['subcontractor_file_id' => $file->id]);
            }
        }

        DB::commit();

        if ($request->type == 0) {
            Session::flash('attach_subcontractor');
        } elseif ($request->type == 1) {
            Session::flash('attach_mat_subcontractor');
        }

        return redirect()->back();
    }

    public function set_work_price(Request $request)
    {

        $work = CommercialOfferWork::find($request->work_id);
        if (! $work) {
            $work = WorkVolumeWork::findOrFail($request->work_id);
        }

        $work->price_per_one = $request->value;
        $work->result_price = $request->value * $work->count;

        $work->save();

        return \GuzzleHttp\json_encode($work->result_price);
    }

    public function set_work_term(Request $request)
    {
        $work = CommercialOfferWork::find($request->work_id);
        if (! $work) {
            $work = WorkVolumeWork::findOrFail($request->work_id);
        }

        $work->term = $request->value;

        $work->save();

        return \GuzzleHttp\json_encode(true);
    }

    public function set_material_price(Request $request)
    {
        $value = (float) str_replace(',', '.', $request->value);

        $material = CommercialOfferMaterialSplit::findOrFail($request->split_id);

        $material->price_per_one = $value;

        $result_price = $value * $request->count;
        $material->result_price = $result_price;
        $material->save();

        return response()->json($result_price);
    }

    public function set_material_used(Request $request)
    {
        $material = CommercialOfferMaterialSplit::findOrFail($request->split_id);

        $material->is_used = $request->value === 'true' ? 1 : 0;

        $material->save();

        return response()->json($material->is_used);
    }

    public function set_nds(Request $request)
    {
        CommercialOffer::where('id', $request->com_offer)->update(['nds' => $request->nds]);

        return \GuzzleHttp\json_encode(true);
    }

    public function toggle_work_mat(Request $request)
    {
        if ($request->type === '1') {
            $commercialOffer = CommercialOffer::find($request->com_offer);
            $commercialOffer->worksForToggling()->where('id', $request->id)->update(['is_hidden' => DB::raw('NOT is_hidden')]);
        } elseif ($request->type === '0') {
            CommercialOfferMaterialSplit::where('id', $request->id)->update(['is_hidden' => DB::raw('NOT is_hidden')]);
        }

        return \GuzzleHttp\json_encode(true);
    }

    public function get_subcontractors(Request $request)
    {
        $contractors = Contractor::query();

        if ($request->q) {
            $contractors->where(function ($contractors) use ($request) {
                $contractors->where('full_name', 'like', '%'.trim($request->q).'%')
                    ->orWhere('short_name', 'like', '%'.trim($request->q).'%')
                    ->orWhere('inn', 'like', '%'.trim($request->q).'%')
                    ->orWhere('kpp', 'like', '%'.trim($request->q).'%');
            });
        }
        $contractors_count = $contractors->count();
        $contractors = $contractors->take(3)->get();

        $results = [[
            'value' => '',
            'text' => 'Показано '.($contractors_count < 3 ? $contractors_count : '3').' из '.$contractors_count.' найденных']];

        foreach ($contractors as $contractor) {
            $results[] = [
                'id' => $contractor->id,
                'text' => $contractor->short_name.', ИНН: '.$contractor->inn,
            ];
        }

        return ['results' => $results];
    }

    public function detach_subcontractors(Request $request)
    {
        DB::beginTransaction();

        if ($request->type === '1') {
            CommercialOfferMaterialSplit::where('subcontractor_file_id', $request->subcontractor_id)->update(['subcontractor_file_id' => null]);
        } else {
            CommercialOfferWork::where('subcontractor_file_id', $request->subcontractor_id)->update(['subcontractor_file_id' => null]);
            //            WorkVolumeWork::where('subcontractor_file_id', $request->subcontractor_id)->update(['subcontractor_file_id' => null]);
        }
        ContractorFile::where('id', $request->subcontractor_id)->delete();

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }

    public function delete_securuty_payment(Request $request)
    {
        CommercialOfferMaterialSplit::findOrFail($request->split_id)->delete();

        return \GuzzleHttp\json_encode(true);
    }

    public function change_security_pay(Request $request)
    {
        $value = (float) str_replace(',', '.', $request->value);
        $material = CommercialOfferMaterialSplit::findOrFail($request->split_id);

        $result_price = $value * $request->mat_count;

        $material->security_price_one = $request->value;

        if ($request->type == 2) {
            $material->security_price_result = $result_price;
        } else {
            $material->security_price_result = $result_price;
        }

        $material->save();

        return \GuzzleHttp\json_encode($result_price);
    }

    public function create_offer_pdf($offer_id, $COtype = 'regular')
    {
        DB::beginTransaction();

        $offer = new CommercialOffer;
        $offer->create_offer_pdf($offer_id, $COtype);

        DB::commit();
    }

    public function set_signer(Request $request, $offer_id)
    {
        $offer = CommercialOffer::findOrFail($offer_id);

        $offer->signer_user_id = $request->signer_user_id;

        $offer->save();

        return \GuzzleHttp\json_encode(true);
    }

    public function set_contact(Request $request, $offer_id)
    {
        $offer = CommercialOffer::findOrFail($offer_id);

        $offer->contact_id = $request->contact_id;

        $offer->save();

        return \GuzzleHttp\json_encode(true);
    }

    public function attach_document(Request $request, $offer_id)
    {
        $this->prepareNotifications = [];
        DB::beginTransaction();

        $offer = CommercialOffer::findOrFail($offer_id);
        if (! $offer->file_name and ! $request->commercial_offer) {
            return back();
        }

        if ($request->commercial_offer) {
            $mime = $request->commercial_offer->getClientOriginalExtension();
            $file_name = 'project-'.$offer->project_id.'_commercial_offer-'.uniqid().'.'.$mime;

            Storage::disk('commercial_offers')->put($file_name, File::get($request->commercial_offer));

            FileEntry::create(['filename' => $file_name, 'size' => $request->commercial_offer->getSize(),
                'mime' => $request->commercial_offer->getClientMimeType(), 'original_filename' => $request->commercial_offer->getClientOriginalName(), 'user_id' => Auth::user()->id, ]);

            $offer->file_name = $file_name;
            $offer->is_uploaded = 1;
        }

        if ($request->budget) {
            $mime = $request->budget->getClientOriginalExtension();
            $file_name = 'project-'.$offer->project_id.'_budget-'.uniqid().'.'.$mime;

            Storage::disk('budget')->put($file_name, File::get($request->budget));

            FileEntry::create(['filename' => $file_name, 'size' => $request->budget->getSize(),
                'mime' => $request->budget->getClientMimeType(), 'original_filename' => $request->budget->getClientOriginalName(), 'user_id' => Auth::user()->id, ]);

            $offer->budget = $file_name;
        }

        if ($request->comment) {
            $offer->comments()->create([
                'comment' => $request->comment,
                'author_id' => Auth::id(),
            ]);
        }

        $offer->status = 2;

        $project = Project::findOrFail($offer->project_id);

        $offer->save();

        if ($offer->is_tongue == 1) {
            foreach ([5, 6] as $group_id) {
                $prev_task = Task::where('target_id', $offer->id)->where('status', 5)->where('is_solved', 0)->first();
                $task = new Task([
                    'project_id' => $offer->project_id,
                    'name' => 'Согласование КП (шпунт)',
                    'responsible_user_id' => Group::find($group_id)->getUsers()->first()->id,
                    //                    'responsible_user_id' => User::where('group_id', $group_id)->first()->id, example of new vacation logic
                    'contractor_id' => $project->contractor_id,
                    'target_id' => $offer->id,
                    'prev_task_id' => $prev_task ? $prev_task->id : null,
                    'expired_at' => $this->addHours(8),
                    'status' => 16,
                ]);

                $task->save();

                $this->prepareNotifications['App\Notifications\CommercialOffer\CustomerApprovalOfOfferSheetPilingTaskNotice'] = [
                    'user_ids' => $task->responsible_user_id,
                    'name' => 'Новая задача «'.$task->name.'»',
                    'additional_info' => "\r\nЗаказчик: ".$project->contractor_name.
                        "\r\nНазвание объекта: ".$project->object->name.
                        "\r\nАдрес объекта: ".$project->object->address,
                    'url' => $task->task_route(),
                    'task_id' => $task->id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                ];

                $task = $offer->unsolved_tasks->where('status', 5)->first();

                if ($task) {
                    $task->solve_n_notify();
                }
            }
        } elseif ($offer->is_tongue == 0) {
            foreach ([5, 73] as $group_id) {
                $prev_task = Task::where('target_id', $offer->id)->where('status', 5)->where('is_solved', 0)->first();
                $task = new Task([
                    'project_id' => $offer->project_id,
                    'name' => 'Согласование КП (сваи)',
                    'responsible_user_id' => Group::find($group_id)->getUsers()->first()->id,
                    'contractor_id' => $project->contractor_id,
                    'target_id' => $offer->id,
                    'prev_task_id' => $prev_task ? $prev_task->id : null,
                    'expired_at' => $this->addHours(8),
                    'status' => 16,
                ]);

                $task->save();

                $this->prepareNotifications['App\Notifications\CommercialOffer\CustomerApprovalOfOfferPileDrivingTaskNotice'] = [
                    'user_ids' => $task->responsible_user_id,
                    'name' => 'Новая задача «'.$task->name.'»',
                    'additional_info' => "\r\n<b>Заказчик:</b> ".$project->contractor_name.
                        "\r\n<b>Название объекта:</b> ".$project->object->name.
                        "\r\n<b>Адрес объекта:</b> ".$project->object->address,
                    'url' => $task->task_route(),
                    'task_id' => $task->id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                ];

                $task = $offer->unsolved_tasks->where('status', 5)->first();

                if ($task) {
                    $task->solve_n_notify();
                }
            }
        }
        DB::commit();

        $this->sendNotifications();

        return redirect()->route('projects::card', $offer->project_id)->with('com_offer', true);
    }

    public function agree_commercial_offer($offer_id)
    {
        $this->prepareNotifications = [];
        DB::beginTransaction();

        $offer = CommercialOffer::findOrFail($offer_id);
        $project = Project::find($offer->project_id);

        $offer->status = 4;

        $offer->save();

        $task = Task::where('project_id', $offer->project_id)->where('status', 6)->where(function ($q) {
            $q->orWhere('is_solved', 0)->orWhere('revive_at', '<>', null);
        })->first();

        if ($task) {
            $this->prepareNotifications['App\Notifications\Task\TaskClosureNotice'] = [
                'user_ids' => $task->responsible_user_id,
                'name' => 'Задача «'.$task->name.'» закрыта',
                'task_id' => $task->id,
                'contractor_id' => $task->project_id ? $project->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? $project->object_id : null,
            ];

            $status_for_humans = [
                'accept' => 'Принято',
                'archive' => 'В архив',
                'change' => 'Требуются изменения',
                'transfer' => 'Перенесено',
            ];

            $task->refresh();

            $this->prepareNotifications['App\Notifications\Task\TaskClosureNotice'] = [
                'user_ids' => Group::find(5/*3*/)->getUsers()->first()->id,
                'name' => 'Задача «'.$task->name.'» закрыта с результатом: '.$status_for_humans['accept'].
                    (is_null($task->revive_at) ? '' : '. Дата, на которую перенесли: '.
                        strftime('%d.%m.%Y', strtotime($task->revive_at))).
                    (is_null($task->final_note) ? '' : '. Комментарий: '.$task->final_note),
                'additional_info' => "\r\nЗаказчик: ".$project->contractor_name.
                    "\r\nНазвание объекта: ".$project->object->name.
                    "\r\nАдрес объекта: ".$project->object->address.
                    "\r\nИсполнитель: ".User::find($task->responsible_user_id)->long_full_name,
                'url' => route('projects::card', [$task->project_id, 'task' => $task->id]),
                'task_id' => $task->id,
                'status' => 2,
                'contractor_id' => $task->project_id ? $project->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? $project->object_id : null,
            ];

            $task->solve();
        }

        if ($project->respUsers()->where('role', ($offer->is_tongue ? 6 : 5))->count() > 0) {
            $mainEngineerOfTonguePostId = 8;
            $mainEngineerOfPilesPostId = 73;

            if ($offer->is_tongue) {
                $mainEngineer = Group::find($mainEngineerOfTonguePostId)->getUsers()->first();
                $taskNameSuffix = '(шпунт)';
                $taskStatus = 25;
            } else {
                $mainEngineer = Group::find($mainEngineerOfPilesPostId)->getUsers()->first();
                $taskNameSuffix = '(сваи)';
                $taskStatus = 24;
            }

            $add_RP_task = Task::create([
                'project_id' => $project->id,
                'name' => 'Назначение ответственного руководителя проектов '.$taskNameSuffix,
                'responsible_user_id' => $mainEngineer ? $mainEngineer->user_id : 6,
                'contractor_id' => $project->contractor_id,
                'target_id' => $offer->id,
                'prev_task_id' => $task ? $task->id : null,
                'status' => $taskStatus,
                'expired_at' => $this->addHours(11),
            ]);

            $this->prepareNotifications['App\Notifications\Task\ProjectLeaderAppointmentTaskNotice'] = [
                'user_ids' => $add_RP_task->responsible_user_id,
                'name' => 'Новая задача «'.$add_RP_task->name.'»',
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $add_RP_task->task_route(),
                'task_id' => $add_RP_task->id,
                'contractor_id' => $add_RP_task->project_id ? Project::find($add_RP_task->project_id)->contractor_id : null,
                'project_id' => $add_RP_task->project_id ?: null,
                'object_id' => $add_RP_task->project_id ? Project::find($add_RP_task->project_id)->object_id : null,
            ];
        }

        $project = Project::findOrFail($offer->project_id);

        $task_created = false;

        if ($project->status < 4 || $project->status == 5) {
            $project->update(['status' => 4]);

            foreach (ProjectResponsibleUser::where('project_id', $project->id)->where('role', 7)->get() as $user) {
                $task = new Task([
                    'project_id' => $offer->project_id,
                    'name' => 'Формирование договоров',
                    'responsible_user_id' => $user->user_id,
                    'description' => 'Коммерческое предложение было одобрено, появилась возможность создавать договора.',
                    'contractor_id' => $project->contractor_id,
                    'expired_at' => $this->addHours(48),
                    'prev_task_id' => isset($task) ? $task->id : null,
                    'target_id' => $offer->id,
                    'status' => 12,
                ]);

                $task->save();

                $this->prepareNotifications['App\Notifications\Task\ContractCreationTaskNotice'] = [
                    'user_ids' => $task->responsible_user_id,
                    'name' => 'Новая задача «'.$task->name.'»',
                    'additional_info' => ' Ссылка на задачу: ',
                    'url' => $task->task_route(),
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                ];

                $task_created = true;
            }
        }

        if (! $task_created) {
            foreach (ProjectResponsibleUser::where('project_id', $project->id)->where('role', 7)->get() as $user) {
                $task = new Task([
                    'project_id' => $offer->project_id,
                    'name' => 'Контроль изменений коммерческого предложения',
                    'responsible_user_id' => $user->user_id,
                    'description' => 'Была одобрена новая версия коммерческого предложения. Вы можете ознакомиться с изменениями.',
                    'contractor_id' => $project->contractor_id,
                    'prev_task_id' => isset($task) ? $task->id : null,
                    'expired_at' => $this->addHours(48),
                    'target_id' => $offer->id,
                    'status' => 12,
                ]);

                $task->save();

                $this->prepareNotifications['App\Notifications\Task\OfferChangeControlTaskNotice'] = [
                    'user_ids' => $task->responsible_user_id,
                    'name' => 'Новая задача «'.$task->name.'»',
                    'additional_info' => ' Ссылка на задачу: ',
                    'url' => $task->task_route(),
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                ];
            }
        }

        DB::commit();

        $this->sendNotifications();

        return redirect()->route('projects::card', $offer->project_id)->with('com_offer', true);
    }

    public function get_offer(Request $request)
    {
        $offer = CommercialOffer::find($request->id);

        return response()->json($offer);
    }

    public function add_manual_note(Request $request, $com_offer_id)
    {
        if ($request->names) {
            foreach ($request->names as $name) {
                $note = new CommercialOfferNote([
                    'note' => $name,
                    'commercial_offer_id' => $com_offer_id,
                ]);
                $note->save();
            }
        }

        return \GuzzleHttp\json_encode(true);
    }

    public function add_manual_requirement(Request $request, $com_offer_id)
    {
        if ($request->names) {
            foreach ($request->names as $name) {
                $note = new CommercialOfferRequirement([
                    'requirement' => $name,
                    'commercial_offer_id' => $com_offer_id,
                ]);
                $note->save();
            }
        }

        return \GuzzleHttp\json_encode(true);
    }

    public function create_double_kp(Request $request)
    {
        $this->prepareNotifications = [];
        DB::beginTransaction();

        $offerPile = CommercialOffer::find($request->secondKP);
        $offerTongue = CommercialOffer::find($request->firstKP);
        $project = Project::find($offerPile->project_id);
        $wvPile = WorkVolume::where('project_id', $project->id)->where('status', 2)->where('type', 1)->first();
        $wvTongue = WorkVolume::where('project_id', $project->id)->where('status', 2)->where('type', 0)->first()->load('shown_materials.parts');
        $doubleCO = CommercialOffer::where('project_id', $project->id)->where('status', 5)->where('is_tongue', 2)->first();

        if (! $doubleCO) {
            // make double WV for double CO
            $wv_double = new WorkVolume();

            $wv_double->user_id = Auth::id();
            $wv_double->project_id = $project->id;
            $wv_double->depth = $wvTongue->depth;
            $wv_double->status = 2;
            $wv_double->version = 1;
            $wv_double->type = 2;

            $wv_double->save();

            // move all old works, relations and materials to double WV
            $worksPile = WorkVolumeWork::where('work_volume_id', $wvPile->id)->get();
            $worksTongue = WorkVolumeWork::where('work_volume_id', $wvTongue->id)->get();
            $works = collect();
            $works = $works->merge($worksPile)->merge($worksTongue);
            $works_materials = WorkVolumeWorkMaterial::whereIn('wv_work_id', $works->pluck('id'))->get();
            $materialsPile = WorkVolumeMaterial::where('work_volume_id', $wvPile->id)->get();
            $materialsTongue = $wvTongue->shown_materials;
            $materials = collect();
            $materials = $materials->merge($materialsPile)->merge($materialsTongue);

            $old_relations = [];
            foreach ($works_materials as $item) {
                $old_relations[] = [$item->wv_material_id => $item->wv_work_id];
            }

            $new_relation = [];
            foreach ($materials as $material) {
                if ($material->material_type != 'complect') {
                    $new_material = $material->replicate();

                    $new_material->work_volume_id = $wv_double->id;
                    $new_material->save();

                    foreach ($old_relations as $item) {
                        $material_id = key($item);

                        if ($material_id == $material->id) {
                            $new_relation[] = [$new_material->id => $item[$material_id]];
                        }
                    }
                } else {
                    $new_complect = $material->replicate();

                    $new_complect->work_volume_id = $wv_double->id;
                    $new_complect->save();

                    foreach ($material->parts as $part) {
                        $new_material = $part->replicate();

                        $new_material->work_volume_id = $wv_double->id;
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

                $new_work->work_volume_id = $wv_double->id;
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
            // get CO versions for double CO version
            $offers_countTongue = CommercialOffer::where('project_id', $project->id)->where('is_tongue', 1)->update(['status' => 3]);
            $offers_countPile = CommercialOffer::where('project_id', $project->id)->where('is_tongue', 0)->update(['status' => 3]);

            // close all tasks for tongue and pile CO
            foreach ([0, 1] as $is_tongue) {
                $offers_id = CommercialOffer::where('project_id', $project->id)->where('is_tongue', $is_tongue)->pluck('id')->toArray();

                $tasks = Task::where('project_id', $project->id)->where('status', 6)->whereIn('target_id', $offers_id)->get();

                foreach ($tasks as $item) {
                    $this->prepareNotifications['App\Notifications\Task\TaskClosureNotice'] = [
                        'user_ids' => $item->responsible_user_id,
                        'name' => 'Задача «'.$item->name.'» закрыта',
                        'task_id' => $item->id,
                        'contractor_id' => $project->contractor_id,
                        'project_id' => $project->id,
                        'object_id' => $project->object_id,
                    ];

                    $item->solve();
                }
            }

            //create new great-super-cool double CO
            $commercial_offer = new CommercialOffer();

            $commercial_offer->name = 'Коммерческое предложение (объединенное)';
            $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 1)->first()->user_id;
            $commercial_offer->project_id = $project->id;
            $commercial_offer->work_volume_id = $wv_double->id;
            $commercial_offer->status = 5;
            $commercial_offer->version = 'Ш'.$offers_countTongue.'С'.$offers_countPile;
            $commercial_offer->file_name = 0;
            $commercial_offer->is_tongue = 2;
            $commercial_offer->contact_id = $offerTongue->contact_id;

            $commercial_offer->save();

            // move old notes, requirements, advancements from tongue and pile CO to new double CO
            foreach ([$offerTongue, $offerPile] as $prev_com_offer) {
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

                if ($prev_com_offer->commercial_offer_works()->count()) {
                    foreach ($prev_com_offer->works as $work) {
                        $new_work = $work->replicate();
                        $new_work->commercial_offer_id = $commercial_offer->id;
                        $new_work->save();
                    }
                } else {
                    foreach ($commercial_offer->work_volume->works as $work) {
                        CommercialOfferWork::create([
                            'work_volume_work_id' => $work->id,
                            'commercial_offer_id' => $commercial_offer->id,
                            'count' => $work->count,
                            'term' => $work->term,
                            'price_per_one' => $work->price_per_one,
                            'result_price' => $work->result_price,
                            'subcontractor_file_id' => $work->subcontractor_file_id,
                            'is_hidden' => $work->is_hidden,
                            'order' => $work->order,
                        ]);
                    }
                }
                //take new materials
                $new_wv_mats = WorkVolumeMaterial::where('work_volume_id', $wv_double->id)->get();
                $split_adapter = $new_wv_mats->groupBy('manual_material_id')->map(function ($group) {
                    return $group->sum('count');
                });

                //get splits from previous com_offer
                $control_count = $prev_com_offer->mat_splits->groupBy('man_mat_id'); //here are old ones
                //creating splits for new commercial_offer
                foreach ($split_adapter as $manual_id => $count) {
                    if (in_array($manual_id, array_keys($control_count->toArray()))) {
                        if (($count == $control_count[$manual_id]->whereIn('type', [1, 3, 5])->sum('count'))) { //if there was no changes amount of
                            foreach ($control_count[$manual_id] as $old_split) {
                                $new_split = $old_split->replicate();
                                $new_split->man_mat_id = $old_split->man_mat_id;
                                $new_split->com_offer_id = $commercial_offer->id;
                                $new_split->save();
                            }
                        } else {
                            CommercialOfferMaterialSplit::create([
                                'man_mat_id' => $manual_id,
                                'count' => $count,
                                'type' => 1,
                                'com_offer_id' => $commercial_offer->id,
                            ]);
                        }
                    }
                }
            }

            $com_offer_request = new CommercialOfferRequest();
            $com_offer_request->user_id = 0;
            $com_offer_request->project_id = $project->id;
            $com_offer_request->commercial_offer_id = $commercial_offer->id;
            $com_offer_request->status = 0;
            $com_offer_request->description = 'Было создано объединенное КП, проверьте актуальность цен';
            $com_offer_request->is_tongue = 2;

            $com_offer_request->save();

            $task_1 = Task::create([
                'project_id' => $commercial_offer->project_id,
                'name' => 'Согласование КП с заказчиком (объединенное)',
                'responsible_user_id' => ProjectResponsibleUser::where('project_id', $commercial_offer->project_id)->where('role', 2)->first()->user_id,
                'contractor_id' => $project->contractor_id,
                'target_id' => $commercial_offer->id,
                'status' => 6,
                'expired_at' => $this->addHours(48),
            ]);

            $this->prepareNotifications['App\Notifications\CommercialOffer\CustomerApprovalOfJointOfferTaskNotice'] = [
                'user_ids' => $task_1->responsible_user_id,
                'name' => 'Новая задача «'.$task_1->name.'»',
                'additional_info' => "\r\nЗаказчик: ".$project->contractor_name.
                    "\r\nНазвание объекта: ".$project->object->name.
                    "\r\nАдрес объекта: ".$project->object->address,
                'url' => $task_1->task_route(),
                'task_id' => $task_1->id,
                'contractor_id' => $project->contractor_id,
                'project_id' => $project->id,
                'object_id' => $project->object_id,
            ];

            // manually create pdf for new offer
            $commercial_offer->create_offer_pdf($commercial_offer->id);

            DB::commit();
        }

        $this->sendNotifications();

        return back();
    }

    public function upload(CommercialOfferReqRequest $request, $project_id)
    {
        if ($request->axios) {
            return back();
        }
        $project = Project::find($project_id);
        if (! $request->has('negotiation_type')) {
            return back();
        }

        $this->prepareNotifications = [];

        DB::beginTransaction();

        $option = $request->option;

        if ($request->com_offer_id_tongue != 'new') {
            $old_offer = CommercialOffer::findOrFail($request->com_offer_id_tongue);
            $option = $old_offer->option;
            $old_offer->decline();
        } elseif ($request->com_offer_id_pile != 'new') {
            $old_offer = CommercialOffer::findOrFail($request->com_offer_id_pile);
            $option = $old_offer->option;
            $old_offer->decline();
        }

        $existing_WVs = WorkVolume::where('project_id', $project_id)->where('option', $option);
        $existing_WVs->update(['status' => 3]);

        // create empty WV
        $parent_WV = new WorkVolume([
            'user_id' => Auth::id(),
            'project_id' => $project_id,
            'status' => 2,
            'is_save_tongue',
            'is_save_pile',
            'option' => $option,
            'depth',
            'type' => ! $request->is_tongue,
            'version' => $existing_WVs->max('version') + 1,
        ]);

        $parent_WV->save();

        // decline all old WVs
        // return abort(403, "Объем работ с таким наименованием уже существует.");

        //Creating commercial offer in status 'Согласовано с заказчиком'
        $offer = new CommercialOffer([
            'project_id' => $project_id,
            'work_volume_id' => $parent_WV->id,
            'name' => 'Коммерческое предложение ('.($request->is_tongue ? 'шпунтовое' : 'свайное').' направление) загружено',
            'file_name' => '0',
            'user_id' => Auth::id(),
            'status' => '1',
            'version' => isset($old_offer) ? $old_offer->version + 1 : 1,
            'is_tongue' => $request->is_tongue,
            'nds' => '20',
            'is_uploaded' => 1,
            'option' => $option,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $offer->save();

        if ($request->commercial_offer) {
            $mime = $request->commercial_offer->getClientOriginalExtension();
            $file_name = 'project-'.$offer->project_id.'_commercial_offer-'.uniqid().'.'.$mime;

            Storage::disk('commercial_offers')->put($file_name, File::get($request->commercial_offer));

            FileEntry::create(['filename' => $file_name, 'size' => $request->commercial_offer->getSize(),
                'mime' => $request->commercial_offer->getClientMimeType(), 'original_filename' => $request->commercial_offer->getClientOriginalName(), 'user_id' => Auth::user()->id, ]);

            $offer->file_name = $file_name;
        }

        if ($request->budget) {
            $mime = $request->budget->getClientOriginalExtension();
            $file_name = 'project-'.$offer->project_id.'_budget-'.uniqid().'.'.$mime;

            Storage::disk('budget')->put($file_name, File::get($request->budget));

            FileEntry::create(['filename' => $file_name, 'size' => $request->budget->getSize(),
                'mime' => $request->budget->getClientMimeType(), 'original_filename' => $request->budget->getClientOriginalName(), 'user_id' => Auth::user()->id, ]);

            $offer->budget = $file_name;
        }

        if ($request->negotiation_type == 1) {
            // add as actual CO
            $offer->to_negotiation(); //send tasks and go further to script
        } elseif ($request->negotiation_type == 2) {
            // add as archived CO
            $offer->status = 4; //or end this com offer

            if ($project->respUsers()->where('role', ($offer->is_tongue ? 6 : 5))->count() > 0) {
                $mainEngineerOfTonguePostId = 8;
                $mainEngineerOfPilesPostId = 58;

                if ($offer->is_tongue) {
                    $mainEngineer = Group::find($mainEngineerOfTonguePostId)->getUsers()->first();
                    $taskNameSuffix = '(шпунт)';
                    $taskStatus = 25;
                } else {
                    $mainEngineer = Group::find($mainEngineerOfPilesPostId)->getUsers()->first();
                    $taskNameSuffix = '(сваи)';
                    $taskStatus = 24;
                }

                $add_RP_task = Task::create([
                    'project_id' => $project->id,
                    'name' => 'Назначение ответственного руководителя проектов '.$taskNameSuffix,
                    'responsible_user_id' => $mainEngineer ? $mainEngineer->user_id : 6,
                    'contractor_id' => $project->contractor_id,
                    'target_id' => $offer->id,
                    'status' => $taskStatus,
                    'expired_at' => $this->addHours(11),
                ]);

                $this->prepareNotifications['App\Notifications\Task\ProjectLeaderAppointmentTaskNotice'] = [
                    'user_ids' => $add_RP_task->responsible_user_id,
                    'name' => 'Новая задача «'.$add_RP_task->name.'»',
                    'additional_info' => ' Ссылка на задачу: ',
                    'url' => $add_RP_task->task_route(),
                    'task_id' => $add_RP_task->id,
                    'contractor_id' => $add_RP_task->project_id ? Project::find($add_RP_task->project_id)->contractor_id : null,
                    'project_id' => $add_RP_task->project_id ? $add_RP_task->project_id : null,
                    'object_id' => $add_RP_task->project_id ? Project::find($add_RP_task->project_id)->object_id : null,
                ];
            }
        }

        $offer->save();

        DB::commit();

        $this->sendNotifications();

        return redirect(route('projects::card', $project_id));
    }

    public function upload_signed_pdf($project_id)
    {
        DB::beginTransaction();
        if (request()->hash) {
            $offer = CommercialOffer::findOrFail(request()->com_offer_id);

            $decoded_pdf_text = base64_decode(request()->hash);
            $path_to_save = storage_path('app/public/docs/commercial_offers');
            $file_name = 'project-'.$project_id.'_commercial_offer-'.uniqid().'.pdf';
            $pdf_file = fopen($path_to_save.'/'.$file_name, 'w');
            $size = fwrite($pdf_file, $decoded_pdf_text);
            fclose($pdf_file);

            FileEntry::create([
                'filename' => $file_name,
                'size' => $size,
                'mime' => 'application/pdf',
                'original_filename' => 'Коммерческое предложение (подписанное)',
                'user_id' => Auth::user()->id,
            ]);

            $offer->file_name = $file_name;
            $offer->is_signed = 1;
            $offer->save();

            DB::commit();

            return \GuzzleHttp\json_encode(true);
        }

        return \GuzzleHttp\json_encode(false);
    }

    public function update_title(Request $request)
    {
        DB::beginTransaction();

        $commercial_offer = CommercialOffer::find($request->commercial_offer);

        $commercial_offer->update(['title' => $request->title]);

        DB::commit();

        return response()->json(true);
    }

    public function get_review(Request $request)
    {
        if (strpos($request->reviewable_type, '.') === false) {
            $review_type = $request->reviewable_type;
        } else {
            $review_type = str_replace('.', '\\', $request->reviewable_type);
        }

        $review = Review::where('reviewable_id', $request->reviewable_id)
            ->where('reviewable_type', $review_type);

        if (in_array($review_type, ['MaterialWorkRelation', 'App\Models\Manual\ManualWork'])) {
            $review->where('commercial_offer_id', $request->commercial_offer_id);
        }
        $review = $review->first();
        $text = '';
        if ($review) {
            $text = $review->review;
        }

        return \GuzzleHttp\json_encode($text);
    }

    public function store_review(Request $request)
    {
        if (strpos($request->form_reviewable_type, '.') === false) {
            $review_type = $request->form_reviewable_type;
        } else {
            $review_type = str_replace('.', '\\', $request->form_reviewable_type);
        }

        Review::updateOrCreate([
            'reviewable_type' => $review_type,
            'reviewable_id' => $request->form_reviewable_id,
            'commercial_offer_id' => $request->commercial_offer_id,
        ],
            ['review' => $request->review,
                'result_status' => ($request->result_status ?? 0)]);

        return \GuzzleHttp\json_encode(true);
    }

    public function make_copy(Request $request, $curr_project_id, $com_offer_id)
    {
        $offer = CommercialOffer::findOrFail($com_offer_id);

        $offer->createCopy($request->project_id, $request->option);

        return redirect(route('projects::card', $request->project_id));
    }

    protected function sendNotifications(): void
    {
        foreach ($this->prepareNotifications as $class => $arguments) {
            try {
                $user_id = $arguments['user_ids'];
                $class::send(
                    $user_id,
                    $arguments
                );
            } catch (\Throwable $throwable) {
                $controllerName = get_class($this);
                $message = "В контроллере $controllerName, не удалось отправить уведомление $class, возникла ошибка: ";
                Log::error($message.$throwable->getMessage());
            }
        }
    }
}
