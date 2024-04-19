<?php

namespace App\Http\Controllers\Commerce;

use App\Events\ContractApproved;
use App\Http\Controllers\Controller;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Contract\Contract;
use App\Models\Contract\ContractCommercialOfferRelation;
use App\Models\Contract\ContractFiles;
use App\Models\Contract\ContractKeyDates;
use App\Models\Contract\ContractKeyDatesPreselectedNames;
use App\Models\Contract\ContractRequest;
use App\Models\Contract\ContractRequestFile;
use App\Models\Contract\ContractThesis;
use App\Models\Contract\ContractThesisVerifier;
use App\Models\Contractors\Contractor;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Notification\Notification;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use App\Models\User;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDF;

class ProjectContractController extends Controller
{
    use TimeCalculator;

    public function card($project_id, $contract_id)
    {
        $contract = Contract::where('contracts.id', $contract_id)
            ->with('theses.verifiers', 'files', 'theses_check.verifiers', 'key_dates', 'commercial_offers', 'operations.materialsPartTo')
            ->leftJoin('commercial_offers', 'commercial_offers.id', 'contracts.commercial_offer_id')
            ->leftJoin('projects', 'projects.id', 'contracts.project_id')
            ->leftJoin('contractors', 'contractors.id', 'projects.contractor_id')
            ->leftJoin('users', 'users.id', '=', 'contracts.user_id')
            ->select('contracts.*', 'users.last_name', 'users.first_name', 'users.patronymic',
                'commercial_offers.file_name as commercial_offer_file', 'projects.name as project_name',
                'projects.contractor_id', 'contractors.short_name as contractor_name')->firstOrFail();

        $user_ids = $contract->theses->pluck('verifiers')->flatten()->pluck('user_id');

        $verifier_users = User::leftJoin('groups', 'groups.id', 'users.group_id')
            ->select('users.id', 'users.group_id', 'users.first_name', 'users.last_name', 'users.patronymic', 'groups.name as group_name')
            ->find($user_ids);

        $responsible_user_ids = ProjectResponsibleUser::where('project_id', $project_id)->where('role', 7)->get();

        $p_users = ProjectResponsibleUser::where('project_id', $project_id)->pluck('user_id')->toArray();

        $subcontract = Contractor::where('id', $contract->subcontractor_id)->first();

        $responsible_users = User::whereIn('users.group_id', [5/*3*/, 53/*16*/, 12/*25*/, 6/*24*/])
            ->orWhereIn('users.id', $responsible_user_ids)
            ->leftJoin('groups', 'groups.id', 'users.group_id')
            ->select('users.id', 'users.group_id', 'users.first_name', 'users.last_name', 'users.patronymic', 'groups.name as group_name');

        $contract_requests = ContractRequest::where('contract_id', $contract_id)
            ->leftJoin('users', 'users.id', '=', 'contract_requests.user_id')
            ->select('contract_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $com_offers_options = CommercialOffer::where('project_id', $project_id)
            ->with('work_volume')
            ->whereHas('work_volume', function($q) {
                $q->where('status', 2);
            })
            ->orderBy('version', 'asc')
            ->groupBy('is_tongue', 'option')
            ->select('commercial_offers.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        return view('projects.contracts.card', [
            'contract' => $contract,
            'com_offers_options' => $com_offers_options,
            'contract_requests' => $contract_requests->get(),
            'responsible_users' => $responsible_users->get(),
            'verifier_users' => $verifier_users,
            'project_resp_user_ids' => $p_users,
            'responsible_user_ids' => $responsible_user_ids->count() > 0 ? $responsible_user_ids->pluck('user_id')->toArray() : [0],
            'subcontract' => $subcontract,
        ]);
    }


    public function edit($project_id, $contract_id)
    {
        $contract = Contract::where('contracts.id', $contract_id)
            ->leftJoin('commercial_offers', 'commercial_offers.id', 'contracts.commercial_offer_id')
            ->leftJoin('projects', 'projects.id', 'contracts.project_id')
            ->leftJoin('contractors', 'contractors.id', 'projects.contractor_id')
            ->leftJoin('users', 'users.id', '=', 'contracts.user_id')
            ->select('contracts.*', 'users.last_name', 'users.first_name', 'users.patronymic', 'commercial_offers.file_name as commercial_offer_file', 'projects.name as project_name', 'projects.contractor_id', 'contractors.short_name as contractor_name');

        $contract->with('theses.verifiers');

        $contract->with('theses_check.verifiers');

        $responsible_user_id = isset(ProjectResponsibleUser::where('project_id', $project_id)
                ->whereIn('role', [5, 6])->first()->user_id) ? ProjectResponsibleUser::where('project_id', $project_id)
            ->whereIn('role', [5, 6])->pluck('user_id') : 0;

        $contract = $contract->first();

        if($responsible_user_id === 0)
        {
            $responsible_user_id = [0];
        }

        $responsible_users = User::whereIn('users.group_id', [5/*3*/, 53/*16*/, 12/*25*/, 6/*24*/])
            ->orWhereIn('users.id', $responsible_user_id)
            ->leftJoin('groups', 'groups.id', 'users.group_id')
            ->select('users.id', 'users.group_id', 'users.first_name', 'users.last_name', 'users.patronymic', 'groups.name as group_name');

        $contract_requests = ContractRequest::where('contract_id', $contract_id)
            ->leftJoin('users', 'users.id', '=', 'contract_requests.user_id')
            ->select('contract_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        return view('projects.contracts.old_edit', [
            'contract' => $contract,
            'contract_requests' => $contract_requests->get(),
            'responsible_users' => $responsible_users->get()
        ]);
    }


    public function decline(Request $request)
    {
        $contract = Contract::with('get_requests.files', 'files')->findOrFail($request->contract_id);
        DB::beginTransaction();

        $new_contr_vers = $contract->replicate();
        $new_contr_vers->version ++;
        $new_contr_vers->status = 1;
        $new_contr_vers->save();

        foreach ($contract->get_requests as $old_request) {
            if ($old_request->status === 1) {
                $new_req = $old_request->replicate();
                $new_req->contract_id = $new_contr_vers->id;
                $new_req->save();
                foreach ($old_request->files as $file) {
                    $new_file = $file->replicate();
                    $new_file->request_id = $new_req->id;
                    $new_file->save();
                }
                $old_request->delete();
            }
        }
        foreach ($contract->files as $file) {
            $new_file = $file->replicate();
            $new_file->contract_id = $new_contr_vers->id;
            $new_file->save();
        }

        foreach ($new_contr_vers->responsible_user_ids as $user_id) {
            $task = new Task([
                'project_id' => $new_contr_vers->project_id,
                'name' => 'Формирование договора: ' . $new_contr_vers->name_for_humans,
                'responsible_user_id' => $user_id->user_id,
                'contractor_id' => Project::find($new_contr_vers->project_id)->contractor_id,
                'target_id' => $new_contr_vers->id,
                'prev_task_id' => $new_contr_vers->get_prev_task()->id,
                'expired_at' => $this->addHours(48),
                'status' => 7
            ]);

            $task->save();

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
            $notification->update([
                'name' => 'Новая задача «' . $task->name . '»',
                'task_id' => $task->id,
                'user_id' => $task->responsible_user_id,
                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                'type' => 39
            ]);
        }
        $solve_tasks = Task::where('target_id', $contract->id)->where('is_solved', 0)->whereIn('status', [8, 9, 10, 13, 11])->get();
        Task::where('target_id', $contract->id)->where('is_solved', 1)->whereIn('status', [8, 9, 10, 13, 11])->where('revive_at', '<>', null)->update(['revive_at' => null]);

        if($solve_tasks->count()) {
            $solve_tasks[0]->result = 3;
            $solve_tasks[0]->final_note = $request->final_note ?? '';
            $solve_tasks[0]->solve_n_notify();

            $solve_tasks->each(function($stask) { $stask->solve();});
        }

        $contract->status = 3;
        $contract->save();

        DB::commit();


        $redirect_path = strpos(url()->previous(), 'tasks') ? route('tasks::index') : route('projects::contract::card', [$new_contr_vers->project_id, $new_contr_vers->id]);

        return redirect($redirect_path);
    }


    public function store(Request $request, $project_id)
    {
        DB::beginTransaction();

        if ($project_id === '0') {
            $project_id = $request->project;
        }

        if ($request->name === 'Иное') {
            $request->name = $request->contract_type_name;
        }

        $contract = new Contract([
            'name' => $request->name,
            'main_contract_id' => json_decode($request->main_contract_id)[0],
            'user_id' => Auth::id(),
            'foreign_id' => $request->foreign_id,
            'project_id' => $project_id,
            'subcontractor_id' => $request->subcontractor_id ? $request->subcontractor_id : 0,
            'ks_date' => $request->ks_date,
            'start_notifying_before' => $request->start_notifying_before,
        ]);


        $contract->save();
        $contract->type = array_search($request->name, $contract->contract_types) ?? $request->contract_type_name;
        $contract->contract_id = Contract::max('contract_id') + 1;

        if ($contract->type == 7 and $contract->main_contract_id) {
            $contract->subcontractor_id = $contract->main_contract->subcontractor_id;
        }

        $contract->save();
        $contract->refresh();

        if ($request->offer_ids) {
            foreach ($request->offer_ids as $offer_id) {
                ContractCommercialOfferRelation::create([
                    'contract_id' => $contract->id,
                    'commercial_offer_id' => $offer_id
                ]);
            }
        }

        $tasks = Task::where([
            'status' => 12,
            'is_solved' => 0,
            'project_id' => $contract->project_id,
        ]);

        $old_task = $tasks->first();
        $tasks->update(['is_solved' => 1]);
        if($old_task){
            Notification::create(['name' => 'Задача «' . $old_task->name . '» закрыта', 'task_id' => $old_task->id, 'user_id' => $old_task->responsible_user_id,
                'contractor_id' => $old_task->project_id ? Project::find($old_task->project_id)->contractor_id : null,
                'project_id' => $old_task->project_id ? $old_task->project_id : null,
                'object_id' => $old_task->project_id ? Project::find($old_task->project_id)->object_id : null,
                'type' => 3
            ]);
        }

        foreach ($contract->responsible_user_ids as $user_id) {
            $task = new Task([
                'project_id' => $contract->project_id,
                'name' => 'Формирование договора: ' . $contract->name_for_humans,
                'responsible_user_id' => $user_id->user_id,
                'target_id' => $contract->id,
                'contractor_id' => Project::find($contract->project_id)->contractor_id,
                'prev_task_id' => $contract->get_prev_task()->id,
                'expired_at' => $this->addHours(48),
                'status' => 7
            ]);

            $task->save();

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
            $notification->update([
                'name' => 'Новая задача «' . $task->name . '»',
                'user_id' => $task->responsible_user_id,
                'task_id' => $task->id,
                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                'type' => 39
            ]);
        }
        DB::commit();

        return back();
    }


    public function update(Request $request, $contract_id)
    {
        DB::beginTransaction();
        $contract = Contract::findOrFail($contract_id);

        if($request->contract_file) {
            $mime = $request->contract_file->getClientOriginalExtension();
            $file_name =  'project-' . $contract->project_id .'contract-' . uniqid() . '.' . $mime;
            Storage::disk('contracts')->put($file_name, File::get($request->contract_file));

            FileEntry::create([
                'filename' => $file_name,
                'size' => $request->contract_file->getSize(),
                'mime' => $request->contract_file->getClientMimeType(),
                'original_filename' => $request->contract_file->getClientOriginalName(),
                'user_id' => Auth::user()->id,
            ]);


            $contract->file_name = $file_name;
        }

        $contract->foreign_id = $request->foreign_id;
        $contract->ks_date = $request->ks_date;
        $contract->start_notifying_before = $request->start_notifying_before;

        $contract->save();

        DB::commit();

        return redirect()->back();
    }


    public function add_thesis(Request $request, $contract_id)
    {
        DB::beginTransaction();

        $thesis = new ContractThesis();
        $contract = Contract::find($contract_id);

        $project_id = $contract->project_id;

        $thesis->user_id = Auth::user()->id;
        $thesis->contract_id = $contract_id;
        $thesis->name = $request->name;
        $thesis->description = $request->description;

        $thesis->save();

        foreach ($request->user_ids as $user_id) {
            $verivier = ContractThesisVerifier::create([
                'user_id' => $user_id,
                'thesis_id' => $thesis->id
            ]);

            $task_created = Task::where('status', 8)
                ->where('target_id', $contract_id)
                ->where('is_solved', 0)
                ->where('responsible_user_id', $user_id)->count();

            if ($task_created === 0) {
                $task = new Task([
                    'project_id' => $project_id,
                    'name' => 'Согласование договора: ' . $contract->name_for_humans,
                    'responsible_user_id' => $user_id,
                    'contractor_id' => Project::find($project_id)->contractor_id,
                    'target_id' => $contract_id,
                    'expired_at' => $this->addHours(2),
                    'prev_task_id' => $contract->tasks()->where('status', 7)->get()->last()->id,
                    'status' => 8
                ]);

                $task->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
                $notification->update([
                    'name' => 'Новая задача «' . $task->name . '»',
                    'task_id' => $task->id,
                    'user_id' => $task->responsible_user_id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                    'type' => 40
                ]);
            }
        }

        DB::commit();

        return redirect()->back();
    }


    public function add_files(Request $request)
    {   //this is about garant file and contract file (last steps of the contract) (approvement)
        //and attach files for the contract at the beginning
        DB::beginTransaction();

        $contract = Contract::find($request->contract_id);
        $mime = $request->document->getClientOriginalExtension();
        $file_name =  'project-' . $contract->project_id .'contract-' . uniqid() . '.' . $mime;

        Storage::disk('contracts')->put($file_name, File::get($request->document));

        FileEntry::create([
            'filename' => $file_name,
            'size' => $request->document->getSize(),
            'mime' => $request->document->getClientMimeType(),
            'original_filename' => $request->document->getClientOriginalName(),
            'user_id' => Auth::user()->id,
        ]);

        if ($request->type === "3") { //just a file for the contract. it comes from first steps of lifecycle. don't do anything with tasks
            ContractFiles::create([
                'name' => $request->name,
                'file_name' => $file_name,
                'original_name' => $request->document->getClientOriginalName(),
                'contract_id' => $contract->id,
            ]);
        } else {
            $old_task = Task::where('project_id', $contract->project_id)->where('is_solved', 0)->where('target_id', $contract->id)->whereIn('status', [9, 10])->first();

            if($old_task) {
                $old_task->result = $request->type;
                $old_task->final_note = $old_task->descriptions[$old_task->status] . $old_task->results[$old_task->status][$old_task->result];
                $old_task->solve_n_notify();
            }
            Task::where('project_id', $contract->project_id)->where('is_solved', 0)->where('target_id', $contract->id)->whereIn('status', [9, 10])->update(['is_solved' => 1]);
            Task::where('target_id', $contract->id)->where('is_solved', 1)->whereIn('status', [9, 10])->where('revive_at', '<>', null)->update(['revive_at' => null]);
        }

        if ($request->type === "1") { //if it is contract file (endpoint of the contract lifecycle)
            $contract->final_file_name = $file_name;
            $contract->status = 6;
        } elseif ($request->type === "2") { //garant file one step before end
            $contract->garant_file_name = $file_name;
            $contract->status = 5;

            foreach ($contract->responsible_user_ids as $user_id) {

                $task = new Task([
                    'project_id' => $contract->project_id,
                    'name' => 'Контроль подписания договора: ' . $contract->name_for_humans . ' (повторно)',
                    'responsible_user_id' => $user_id->user_id,
                    'contractor_id' => Project::find($contract->project_id)->contractor_id,
                    'target_id' => $contract->id,
                    'prev_task_id' => $contract->get_prev_task()->id,
                    'expired_at' => $this->addHours(168),
                    'status' => 10
                ]);

                $task->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
                $notification->update([
                    'name' => 'Новая задача «' . $task->name . '»',
                    'task_id' => $task->id,
                    'user_id' => $task->responsible_user_id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                    'type' => 42
                ]);
            }
        }
        $contract->ks_date = $request->ks_date;

        $contract->save();
        if (in_array($contract->status, [5, 6])) {
            event((new ContractApproved())->createTasksForMatAcc($contract));
        }

        DB::commit();

        $redirect_path = strpos(url()->previous(), 'tasks') ? route('tasks::index') : url()->previous();

        return redirect($redirect_path);
    }


    public function delete_thesis(Request $request)
    {
        DB::beginTransaction();

        ContractThesisVerifier::where('thesis_id', $request->thesis_id)->delete();
        ContractThesis::find($request->thesis_id)->delete();

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }


    public function update_thesis(Request $request)
    {
        DB::beginTransaction();

        $thesis = ContractThesis::findOrFail($request->thesis_id);

        $contract_id = $thesis->contract_id;

        $contract = Contract::find($contract_id);

        $project_id = $contract->project_id;

        $thesis->name = $request->name;
        $thesis->description = $request->description;
        $thesis->status = 1;

        $thesis->save();

        $old_thesis_verifiers = $thesis->get_verifiers;
        $deleted_user_ids = array_diff($old_thesis_verifiers->pluck('user_id')->toArray(), $request->user_ids);

        $thesis->get_verifiers()->delete();
        $old_thesis_tasks = Task::where('status', 8)->where('target_id', $contract_id)->where('is_solved', 0)->get();

        foreach ($request->user_ids as $user_id) {
            $verifier = ContractThesisVerifier::create([
                'user_id' => $user_id,
                'thesis_id' => $thesis->id
            ]);
            if (!in_array($user_id, $old_thesis_tasks->pluck('responsible_user_id')->toArray())) {

                $task = new Task([
                    'project_id' => $project_id,
                    'name' => 'Согласование договора: ' . $contract->name_for_humans,
                    'responsible_user_id' => $user_id,
                    'contractor_id' => Project::find($project_id)->contractor_id,
                    'target_id' => $contract_id,
                    'prev_task_id' => $contract->tasks()->where('status', 7)->get()->last()->id,
                    'expired_at' => $this->addHours(2),
                    'status' => 8
                ]);

                $task->save();

                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
                $notification->update([
                    'name' => 'Новая задача «' . $task->name . '»',
                    'task_id' => $task->id,
                    'user_id' => $task->responsible_user_id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                    'type' => 40
                ]);
            }
        }

        DB::commit();

        return redirect()->back();
    }


    public function agree_thesis($thesis_id)
    {
        DB::beginTransaction();

        $thesis = ContractThesis::with('verifiers')->where('id', $thesis_id)->first();
        $contract = Contract::with('get_requests.files', 'theses')->find($thesis->contract_id);

        $verifier = ContractThesisVerifier::where('user_id', Auth::user()->id)
            ->where('thesis_id', $thesis_id)
            ->update(['status' => 3]);

        if ($thesis->verifiers->where('status', '!=', 1)->count() + 1 == $thesis->verifiers->count()) {
            if ($thesis->get_verifiers->where('status', 2)->count()) {
                $thesis->status = 2;
            } else {
                $thesis->status = 3;
            }
            $thesis->save();
        }

        $check = ContractThesisVerifier::where('user_id', Auth::id())->whereIn('thesis_id', $contract->theses->pluck('id'))->where('status', 1)->get();

        if (!$check->count()) {
            $task = Task::where('project_id', $contract->project_id)->where('status', 8)->where('is_solved', 0)->where('responsible_user_id', Auth::id())->first();
            $task->result = 1;
            $task->final_note = $task->descriptions[$task->status] . $task->results[$task->status][$task->result];
            $task->solve_n_notify();
        }

        DB::commit();

        return redirect()->back();
    }


    public function reject_thesis(Request $request)
    {
        DB::beginTransaction();

        $thesis = ContractThesis::findOrFail($request->thesis_id);

        $contract = Contract::with('get_requests.files')->findOrFail($thesis->contract_id);

        $verifier = ContractThesisVerifier::where('user_id', Auth::user()->id)
            ->where('thesis_id', $request->thesis_id);

        $verifier->update(['status' => 2]);

        if ($thesis->verifiers->where('status', '!=', 1)->count() == $thesis->verifiers->count()) {
            $thesis->status = 2;
            $thesis->save();
        }

        $check = ContractThesisVerifier::where('user_id', Auth::id())->whereIn('thesis_id', $contract->theses->pluck('id'))->where('status', 1)->get();

        if (!$check->count()) {
            $task = Task::where('project_id', $contract->project_id)->where('status', 8)->where('is_solved', 0)->where('responsible_user_id', Auth::id())->first();
            $task->result = 2;
            $task->final_note = $task->descriptions[$task->status] . $task->results[$task->status][$task->result];
            $task->solve_n_notify();
        }

        $reject = new ContractRequest();

        $reject->name = $request->name;
        $reject->project_id = $contract->project_id;
        $reject->contract_id = $contract->id;
        $reject->user_id = Auth::user()->id;
        $reject->thesis_id = $verifier->first()->id;
        $reject->description = $request->description;

        $reject->save();

        if ($request->documents) {
            foreach($request->documents as $document) {
                $file = new ContractRequestFile();

                $mime = $document->getClientOriginalExtension();
                $file_name =  'project-' . $reject->project_id .'request_file-' . uniqid() . '.' . $mime;

                Storage::disk('contract_request_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->request_id = $reject->id;
                $file->is_result = 0;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }

        DB::commit();

        return back();
    }


    public function approve(Request $request, $project_id, $contract_id)
    {
        DB::beginTransaction();

        $contract = Contract::where('id', $request->contract_id)->first();
        $contract->status = 4;
        $contract->save();

        foreach ($contract->responsible_user_ids as $user_id) {

            $task = new Task([
                'project_id' => $contract->project_id,
                'name' => 'Контроль подписания договора: ' . $contract->name_for_humans,
                'responsible_user_id' => $user_id->user_id,
                'contractor_id' => Project::find($contract->project_id)->contractor_id,
                'target_id' => $contract->id,
                'prev_task_id' => $contract->get_prev_task()->id,
                'expired_at' => $this->addHours(168),
                'status' => 9
            ]);

            $task->save();

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
            $notification->update([
                'name' => 'Новая задача «' . $task->name . '»',
                'task_id' => $task->id,
                'user_id' => $task->responsible_user_id,
                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                'type' => 41
            ]);
        }
        $solve_task = Task::where('project_id', $contract->project_id)->where('status', 11)->where('is_solved', 0)->where('target_id', $contract->id)->get();

        if($solve_task->count()) {
            $solve_task[0]->result = 1;
            $solve_task[0]->final_note = $solve_task[0]->descriptions[$solve_task[0]->status] . $solve_task[0]->results[$solve_task[0]->status][$solve_task[0]->result];

            $solve_task[0]->solve_n_notify();
        }

        Task::where('project_id', $contract->project_id)->where('status', 11)->where('is_solved', 0)->where('target_id', $contract->id)->update(['is_solved' => 1]);
        Task::where('target_id', $contract->id)->where('is_solved', 1)->whereIn('status', [11])->where('revive_at', '<>', null)->update(['revive_at' => null]);

        DB::commit();

        $redirect_path = strpos(url()->previous(), 'tasks') ? route('tasks::index') : route('projects::card', $contract->project_id);

        return redirect($redirect_path);
    }


    public function send_contract(Request $request, $contract_id)
    {
        DB::beginTransaction();

        $contract = Contract::findOrFail($request->contract_id);

        $contract->status = 2;

        $contract->save();

        $task = new Task([
            'project_id' => $contract->project_id,
            'name' => 'Контроль согласования договора: ' . $contract->name_for_humans,
            'responsible_user_id' => Auth::id(),
            'contractor_id' => Project::find($contract->project_id)->contractor_id,
            'target_id' => $contract->id,
            'prev_task_id' => $contract->get_prev_task()->id,
            'expired_at' => $this->addHours(2),
            'status' => 11
        ]);

        $task->save();

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->update([
            'name' => 'Новая задача «' . $task->name . '»',
            'task_id' => $task->id,
            'user_id' => $task->responsible_user_id,
            'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
            'project_id' => $task->project_id ? $task->project_id : null,
            'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
            'type' => 51
        ]);

        $solve_task = Task::where('project_id', $contract->project_id)->where('status', 7)->where('is_solved', 0)->where('target_id', $contract->id)->get();

        if($solve_task->count()) {
            if ($solve_task[0]->responsible_user_id != 6) {
                Notification::create([
                    'name' => 'Задача «' . $solve_task[0]->name . '» закрыта',
                    'task_id' => $solve_task[0]->id,
                    'user_id' => $solve_task[0]->responsible_user_id,
                    'contractor_id' => $solve_task[0]->project_id ? Project::find($solve_task[0]->project_id)->contractor_id : null,
                    'project_id' => $solve_task[0]->project_id ? $solve_task[0]->project_id : null,
                    'object_id' => $solve_task[0]->project_id ? Project::find($solve_task[0]->project_id)->object_id : null,
                    'type' => 3
                ]);
            }
        }
        Task::where('project_id', $contract->project_id)->where('status', 7)->where('is_solved', 0)->where('target_id', $contract->id)->update(['is_solved' => 1]);

        DB::commit();

        return \GuzzleHttp\json_encode(true);
    }


    public function request_store(Request $request, $project_id)
    {
        DB::beginTransaction();

        $contract_id = $request->contract_id;
        if ($contract_id === "0") {
            $contract = new Contract([
                'name' => $request->name,
                'project_id' => $project_id,
                'commercial_offer_id' => CommercialOffer::where('status', 4)->first()->id,
                'user_id' => auth()->id(),
            ]);

            $contract->save();
            $contract->contract_id = Contract::max('contract_id') + 1;
            $contract->save();
        }

        $contract_request = new ContractRequest([
            'name' => $request->name,
            'project_id' => $project_id,
            'contract_id' => $contract_id,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        $contract_request->save();

        if ($request->documents) {
            foreach($request->documents as $document) {
                $file = new ContractRequestFile();

                $mime = $document->getClientOriginalExtension();
                $file_name =  'project-' . $contract_request->project_id .'request_file-' . uniqid() . '.' . $mime;

                Storage::disk('contract_request_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->request_id = $contract_request->id;
                $file->is_result = 0;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }

        if ($request->project_documents) {
            $project_docs = ProjectDocument::whereIn('id', $request->project_documents)->get();

            foreach($request->project_documents as $document_id) {
                $file = new ContractRequestFile();

                $file->file_name = $project_docs->where('id', $document_id)->first()->file_name;
                $file->request_id = $contract_request->id;
                $file->is_result = 0;
                $file->original_name = $project_docs->where('id', $document_id)->first()->name;
                $file->is_proj_doc = 1;

                $file->save();
            }
        }

        DB::commit();

        return back();
    }


    public function request_update(Request $request)
    {
        DB::beginTransaction();

        $contract_request = ContractRequest::findOrFail($request->contract_request_id);

        if (isset($request->status)) {
            $contract_request->status = $request->status == 'confirm' ? 2 : 3;
            $contract_request->result_comment = $request->result_comment;
        }


        if ($request->documents) {
            foreach($request->documents as $document) {
                $file = new ContractRequestFile();

                $mime = $document->getClientOriginalExtension();
                $file_name =  'project-' . $contract_request->project_id .'request_file-' . uniqid() . '.' . $mime;

                Storage::disk('contract_request_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->request_id = $contract_request->id;
                $file->is_result = 1;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }

        if ($request->project_documents) {
            $project_docs = ProjectDocument::whereIn('id', $request->project_documents)->get();

            foreach($request->project_documents as $document_id) {
                $file = new ContractRequestFile();

                $file->file_name = $project_docs->where('id', $document_id)->first()->file_name;
                $file->request_id = $contract_request->id;
                $file->is_result = 1;
                $file->original_name = $project_docs->where('id', $document_id)->first()->name;
                $file->is_proj_doc = 1;

                $file->save();
            }
        }

        $contract_request->save();

        DB::commit();

        return back();
    }


    public function get_reject_info(Request $request)
    {
        $reject = ContractRequest::where('user_id', $request->user_id)
            ->where('thesis_id', $request->thesis_id)
            ->first();

        return \GuzzleHttp\json_encode($reject);
    }


    public function delete_file(Request $request)
    {
        ContractFiles::find($request->file_id)->delete();

        return back();
    }

    public function contract_delete_request(Request $request)
    {
        DB::beginTransaction();

        // check remove task existence
        if (Task::where('status', 20)->where('target_id', $request->contract_id)->where('is_solved', 0)->first()) {
            return back();
        }

        $contract = Contract::with('project')->findOrFail($request->contract_id);
        // create task
        $task = Task::create([
            'name' => 'Контроль удаления договора ' . $request->contract_name,
            'description' => 'Пользователь ' . Auth::user()->full_name .
                ' отправил заявку на удаление договора с комментарием: ' . $request->reason .
                '. Необходимо подтвердить или отклонить удаление договора',
            'responsible_user_id' => Group::find( 5/*3*/)->getUsers()->first()->id,
            'user_id' => Auth::id(),
            'contractor_id' => $contract->project->contractor_id,
            'project_id' => $contract->project->id,
            'target_id' => $request->contract_id,
            'expired_at' => $this->addHours(48),
            'status' => 20
        ]);

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = ' Ссылка на задачу: ' . $task->task_route();
        $notification->update([
            'name' => 'Новая задача «' . $task->name . '. ' . $task->description . '»',
            'task_id' => $task->id,
            'user_id' => $task->responsible_user_id,
            'contractor_id' => $task->contractor_id,
            'project_id' => $contract->project->id,
            'object_id' => $contract->project->object_id,
            'type' => 43
        ]);

        DB::commit();

        return back();
    }

    public function key_names()
    {
        $keyDateNames = ContractKeyDatesPreselectedNames::query();

        if (request('q')) {
            $keyDateNames->where('value', 'like', '%' . request('q') . '%');
        }

        $keyDateNames = $keyDateNames->get();

        $results = [];
        foreach ($keyDateNames as $name) {
            $results[] = [
                'id' => $name->id,
                'text' => $name->value,
            ];
        }

        return ['results' => $results];
    }

    public function key_date_fork($contract_id)
    {
//        dd(request()->all(), $this->isUpdateRequest(), round(request('sum'), 2));
        if ($this->isUpdateRequest())
            return $this->updateExistingKeyDate();

        return $this->createNewContractKeyDate($contract_id);
    }

    public function isUpdateRequest(): bool
    {
        return request('key_id') and !request('parent_key_id') || request('key_id') != request('parent_key_id');
    }

    public function updateExistingKeyDate()
    {
        $keyDate = ContractKeyDates::findOrFail(request('key_id'));
        $keyDate->update([
            'name' => request('name'),
            'sum' => request('sum') ? round(request('sum'), 2) : null,
            'date_from' => request('date_from') ? Carbon::createFromFormat('d.m.Y', request('date_from')) : null,
            'date_to' => request('date_to') ? Carbon::createFromFormat('d.m.Y', request('date_to')) : null,
            'note' => substr(request('note'), 0, 300),
        ]);

        if ($this->isNewKeyDateName()) {
            $this->getAddNewContractKeyDatesNameValue(request('name'));
        }

        return response()->json($keyDate);
    }

    public function createNewContractKeyDate($contract_id)
    {
        DB::beginTransaction();

        $keyDate = ContractKeyDates::create([
            'contract_id' => $contract_id,
            'key_date_id' => request('parent_key_id'),
            'name' => request('name'),
            'sum' => request('sum') ? round(request('sum'), 2) : null,
            'date_from' => request('date_from') ? Carbon::createFromFormat('d.m.Y', request('date_from')) : null,
            'date_to' => request('date_to') ? Carbon::createFromFormat('d.m.Y', request('date_to')) : null,
            'note' => substr(request('note'), 0, 300),
        ]);

        if ($this->isNewKeyDateName()) {
            $this->getAddNewContractKeyDatesNameValue(request('name'));
        }

        DB::commit();

        return response()->json($keyDate);
    }

    public function isNewKeyDateName(): bool
    {
        return ! in_array(request('name'), ContractKeyDatesPreselectedNames::query()->pluck('value')->toArray());
    }

    public function getAddNewContractKeyDatesNameValue(string $value): void
    {
        ContractKeyDatesPreselectedNames::create(['value' => $value]);
    }

    public function remove_key_date()
    {
        DB::beginTransaction();

        $date = ContractKeyDates::findOrFail(request('id'));
        $date->related_key_dates()->delete();
        $date->delete();

        DB::commit();

        return response()->json(true);
    }

    public function attach_com_offers(Request $request, $contract_id)
    {
        ContractCommercialOfferRelation::where('contract_id', $request->$contract_id)->delete();

        if ($request->offer_ids) {
            foreach ($request->offer_ids as $offer_id) {
                ContractCommercialOfferRelation::create([
                    'contract_id' => $contract_id,
                    'commercial_offer_id' => $offer_id
                ]);
            }
        }

        return redirect()->back();
    }

}
