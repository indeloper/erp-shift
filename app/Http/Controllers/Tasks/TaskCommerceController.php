<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\CommercialOffer\CommercialOfferRequest;
use App\Models\CommercialOffer\CommercialOfferWork;
use App\Models\Contract\Contract;
use App\Models\Contract\ContractFiles;
use App\Models\Contract\ContractRequest;
use App\Models\Contract\ContractRequestFile;
use App\Models\Contract\ContractThesis;
use App\Models\Contract\ContractThesisVerifier;
use App\Models\ExtraDocument;
use App\Models\Group;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectObject;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Models\WorkVolume\WorkVolumeRequest;
use App\Notifications\Task\TaskPostponedAndClosedNotice;
use App\Services\Commerce\SplitService;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskCommerceController extends Controller
{
    use TimeCalculator;

    public function common_task(Request $request, $id)
    {
        $task = Task::where('tasks.id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftJoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'),
                'projects.name as project_name', 'project_objects.address as project_address', 'project_objects.name as object_name',
                'contractors.short_name as contractor_name', 'tasks.*')
            ->firstOrfail();

        // now we can see other's solved tasks
        //if ($task->responsible_user_id != Auth::user()->id) {
        //    abort(403);
        //}

        $task->update(['is_seen' => 1]);

        $project_docs = ProjectDocument::where('project_id', $task->project_id)
            ->leftjoin('users', 'users.id', '=', 'project_documents.user_id')
            ->select('project_documents.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'))
            ->get();

        $extra_documents = ExtraDocument::orderBy('version', 'desc')
            ->where('project_id', $task->project_id)
            ->leftjoin('users', 'users.id', '=', 'extra_documents.user_id')
            ->select('extra_documents.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'))
            ->get();

        $pq = Project::where('projects.id', $task->project_id);

        $project = $pq->first();

        $work_volumes = WorkVolume::where('project_id', $project->id)->where('type', '!=', 2)->get();
        $com_offers = CommercialOffer::where('project_id', $project->id)->whereIn('status', [1, 2, 3, 4, 5])->get();

        if ($com_offers->count()) {
            if (in_array($com_offers[0]->work_volume_id, $work_volumes->pluck('id')->toArray())) {
                $pq->leftjoin('commercial_offers', 'commercial_offers.project_id', '=', 'projects.id')
                    ->leftjoin('work_volumes', 'work_volumes.id', '=', 'commercial_offers.work_volume_id')
                    ->whereIn('work_volumes.status', [1, 2, 3])
                    ->whereIn('commercial_offers.status', [1, 2, 3, 4, 5]);
                if ($com_offers[0]->file_name) {
                    $pq->select('projects.id as project_id', 'projects.name as project_name', 'work_volumes.id as work_volume_id', 'commercial_offers.id as commercial_offer_id', 'commercial_offers.file_name as commercial_offer_file');
                } else {
                    $pq->select('projects.id as project_id', 'projects.name as project_name', 'work_volumes.id as work_volume_id', 'commercial_offers.id as commercial_offer_id');
                }
            } else {
                $pq->leftjoin('commercial_offers', 'commercial_offers.project_id', '=', 'projects.id')
                    ->whereIn('commercial_offers.status', [1, 2, 3, 4, 5])
                    ->select('projects.id as project_id', 'projects.name as project_name', 'commercial_offers.id as commercial_offer_id');
            }

        } elseif ($work_volumes->count()) {
            $pq->leftjoin('work_volumes', 'work_volumes.project_id', '=', 'projects.id')
                ->whereIn('work_volumes.status', [1, 2])
                ->select('projects.id as project_id', 'projects.name as project_name', 'work_volumes.id as work_volume_id');
        }

        if ($task->status === 3) {
            $target = route('projects::work_volume::edit_tongue', [$task->project_id, $task->target_id]);
            $work_volume_requests = WorkVolumeRequest::where('work_volume_id', $task->target_id)->where('tongue_pile', 0)
                ->leftJoin('users', 'users.id', '=', 'work_volume_requests.user_id')
                ->select('work_volume_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')->with('files')->get();
        }

        if ($task->status === 4) {
            $target = route('projects::work_volume::edit_pile', [$task->project_id, $task->target_id]);
            $work_volume_requests = WorkVolumeRequest::where('work_volume_id', $task->target_id)->where('tongue_pile', 1)
                ->leftJoin('users', 'users.id', '=', 'work_volume_requests.user_id')
                ->select('work_volume_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')->with('files')->get();
        }

        if ($task->status === 5) {
            $uploaded_CO = $com_offers->find($task->target_id);
            $comments = $uploaded_CO->work_volume->requests()->get()->pluck('result_comment');

            $show_comments = false;
            if ($task->prev_task) {
                $show_comments = $task->prev_task->status == 18;
            }
            $target = route('projects::commercial_offer::'.($uploaded_CO ? ($uploaded_CO->is_uploaded ? 'card_'.($uploaded_CO->is_tongue ? 'tongue' : 'pile') : 'edit') : 'edit'), [$task->project_id, $task->target_id]);
            $commercial_offer_requests = CommercialOfferRequest::where('project_id', $task->project_id)->where('commercial_offer_id', $task->target_id)
                ->where('commercial_offer_requests.status', 0)
                ->leftJoin('users', 'users.id', '=', 'commercial_offer_requests.user_id')
                ->select('commercial_offer_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')->get();
        }

        if ($task->status === 6) {
            $target = route('projects::commercial_offer::agree_commercial_offer', $task->target_id);
        }

        if ($task->status >= 7 and $task->status <= 11) {
            $target = route('projects::contract::card', [$task->project_id, $task->target_id]);
            $contract_requests = ContractRequest::where('contract_id', $task->target_id)
                ->leftJoin('users', 'users.id', '=', 'contract_requests.user_id')
                ->select('contract_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
                ->with('files')->get();
        }

        if ($task->status === 12) {
            $target = route('tasks::solve_task', $task->id);
        }

        if ($task->status === 14) {
            $target = route('projects::card', [$task->project_id, 'task_14']);

            $pq->leftjoin('project_responsible_users', 'project_responsible_users.project_id', '=', 'projects.id')
                ->where('project_responsible_users.role', '=', 4)
                ->leftjoin('users', 'users.id', '=', 'project_responsible_users.user_id')
                ->select('projects.id as project_id', 'projects.name as project_name', 'project_responsible_users.user_id', 'users.id as user_id',
                    'users.last_name', 'users.first_name', 'users.patronymic', 'work_volumes.id as work_volume_id');
        }

        if (in_array($task->status, [24, 25])) {
            $target = route('projects::commercial_offer::card_'.($task->status === 25 ? 'tongue' : 'pile'), [$task->project_id, $task->target_id]);
            $sop = ProjectResponsibleUser::where('project_id', $pq->first()->project_id)->where('role', $task->status === 24 ? 5 : 6)->first();

        }

        if ($task->status === 15) {
            $target = route('projects::card', [$task->project_id, 'task_14']);

            $pq->leftjoin('project_responsible_users', 'project_responsible_users.project_id', '=', 'projects.id')
                ->where('project_responsible_users.role', '=', 2)
                ->leftjoin('users', 'users.id', '=', 'project_responsible_users.user_id')
                ->select('projects.id as project_id', 'projects.name as project_name', 'project_responsible_users.user_id', 'users.id as user_id',
                    'users.last_name', 'users.first_name', 'users.patronymic', 'work_volumes.id as work_volume_id');
        }

        if ($task->status === 16) {
            $target = route('projects::card', [$project->id, 'task_16']);
            $pq->select('projects.id as project_id', 'projects.name as project_name', 'work_volumes.id as work_volume_id',
                'commercial_offers.id as commercial_offer_id', 'commercial_offers.file_name as commercial_offer_file', 'projects.is_important');
        }

        if ($task->status === 17) {
            $wv_request = WorkVolumeRequest::where('work_volume_requests.id', $task->target_id)
                ->leftJoin('users', 'users.id', '=', 'work_volume_requests.user_id')
                ->select('work_volume_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
                ->with('files', 'wv', 'user')->first();
            $route = stristr($task->name, 'шпунт') ? 'projects::work_volume::edit_tongue' : 'projects::work_volume::edit_pile';
            $target = route($route, [$task->project_id, $task->target_id]);
        }

        if ($task->status === 18) {
            $route = stristr($task->name, 'шпунт') ? 'projects::work_volume::edit_tongue' : 'projects::work_volume::edit_pile';
            $target = route($route, [$task->project_id, $task->target_id]);
            $wv_request = WorkVolume::find($task->target_id); // actually, this is WV, not WV request
            $sop = ProjectResponsibleUser::where('project_id', $pq->first()->project_id)->where('role', 2)->first(); // specially for this task
        }

        if ($task->status === 20) {
            $target = route('projects::contract::card', [$task->project_id, $task->target_id]);
        }

        $task_files = TaskFile::where('task_files.task_id', $id)
            ->leftJoin('users', 'users.id', '=', 'task_files.user_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'), 'task_files.*');

        $contract = Contract::where('id', $task->target_id)->first();

        return view('tasks.common_task', [
            'task' => $task,
            'target' => $target ?? '',
            'contract' => $contract,
            'work_volumes' => $work_volumes,
            'com_offers' => $com_offers,
            'project' => $pq->first(),
            'project_docs' => $project_docs,
            'task_files' => $task_files->get(),
            'extra_documents' => $extra_documents,
            'comments' => isset($comments) ? $comments : [],
            'show_comments' => isset($show_comments) ? $show_comments : false,
            'wv_request' => isset($wv_request) ? $wv_request : '',
            'sop' => isset($sop) ? $sop : null,
            'work_volume_requests' => isset($work_volume_requests) ? $work_volume_requests : null,
            'commercial_offer_requests' => isset($commercial_offer_requests) ? $commercial_offer_requests : null,
            'contract_requests' => isset($contract_requests) ? $contract_requests : null,
            'wv_responsible' => $task->project->respUsers()->whereRole(4)->first()->user ?? '',
        ]);
    }

    public function solve_task(Request $request, $task_id)
    {
        DB::beginTransaction();

        /** Собираем уведомления для отправки после коммита транзакции */
        $prepareNotifications = [];

        if ($task_id == 0) {
            $com_offer = CommercialOffer::findOrFail($request->com_offer_id);
            $task = $com_offer->tasks()->where('status', 16)->where('is_solved', 0)->where('responsible_user_id', Auth::user()->id)->first();
        } else {
            $task = Task::findOrFail($task_id);
        }

        $events_cache = collect([]);

        $project = new Project();
        if ($task->project_id) {
            $project = Project::find($task->project_id);
        }

        if ($task->status == 16) {
            $com_offer = CommercialOffer::findOrFail($task->target_id);
            $project = Project::find($com_offer->project_id);

            if ($request->status_result === 'accept') {
                $task->result = 1;
                $task->final_note = (is_null($request->final_note) ? $task->descriptions[$task->status].$task->results[$task->status][$task->result] : $request->final_note);
                $task->save();

                $prev_task = $task;

                if ($com_offer->is_tongue == 1) {
                    $com_offer->status = 5;
                    $com_offer->save();

                    // here we catch uploadedCO
                    if (! $com_offer->is_uploaded && ! $com_offer->is_signed) {
                        $com_offer->create_offer_pdf($com_offer->id, $COtype = 'regular', $from_task = true);
                    }

                    $tasks = Task::where('project_id', $project->id)->where('status', 16)->where('target_id', $com_offer->id)->where('is_solved', 0)->get();
                    foreach ($tasks as $item) {
                        $item->result = 1;
                        $item->solve_n_notify();
                    }

                    $task_1 = new Task([
                        'project_id' => $com_offer->project_id,
                        'name' => 'Согласование КП с заказчиком (шпунт)',
                        'responsible_user_id' => ProjectResponsibleUser::where('project_id', $com_offer->project_id)->where('role', 2)->first()->user_id,
                        'contractor_id' => $project->contractor_id,
                        'target_id' => $com_offer->id,
                        'prev_task_id' => $prev_task->id,
                        'expired_at' => $this->addHours(48),
                        'status' => 6,
                        'description' => $request->final_note ? 'Комментарий от '.Auth::user()->full_name.': '.$request->final_note : '',
                    ]);

                    $task_1->save();

                    $prepareNotifications['App\Notifications\CommercialOffer\CustomerApprovalOfOfferSheetPilingTaskNotice'] = [
                        'user_ids' => $task_1->responsible_user_id,
                        'name' => 'Новая задача «'.$task_1->name.'»',
                        'additional_info' => ' Ссылка на задачу: ',
                        'url' => $task_1->task_route(),
                        'task_id' => $task_1->id,
                        'contractor_id' => $task_1->project_id ? Project::find($task_1->project_id)->contractor_id : null,
                        'project_id' => $task_1->project_id ? $task_1->project_id : null,
                        'object_id' => $task_1->project_id ? Project::find($task_1->project_id)->object_id : null,
                    ];
                    $project->status = 8;
                    $project->save();
                } elseif ($com_offer->is_tongue == 0) {
                    if (Auth::user()->isInGroup(5, 6, 73)/*3*/) {
                        // sign our CO
                        $com_offer->status = 5;
                        $com_offer->save();

                        // here we catch uploadedCO
                        if (! $com_offer->is_uploaded) {
                            $com_offer->create_offer_pdf($com_offer->id, $COtype = 'regular', $from_task = true);
                        }

                        $tasks = Task::where('project_id', $project->id)->where('status', 16)->where('target_id', $com_offer->id)->where('is_solved', 0)->get();
                        foreach ($tasks as $item) {
                            $item->result = 1;
                            $item->solve_n_notify();
                        }

                        $new_task = new Task([
                            'project_id' => $com_offer->project_id,
                            'name' => 'Согласование КП с заказчиком (сваи)',
                            'responsible_user_id' => ProjectResponsibleUser::where('project_id', $com_offer->project_id)->where('role', 1)->first()->user_id,
                            'contractor_id' => $project->contractor_id,
                            'target_id' => $com_offer->id,
                            'prev_task_id' => $prev_task->id,
                            'expired_at' => $this->addHours(48),
                            'status' => 6,
                            'description' => $request->final_note ? 'Комментарий от '.Auth::user()->full_name.': '.$request->final_note : '',
                        ]);

                        $new_task->save();

                        $prepareNotifications['App\Notifications\CommercialOffer\CustomerApprovalOfOfferPileDrivingTaskNotice'] = [
                            'user_ids' => $new_task->responsible_user_id,
                            'name' => 'Новая задача «'.$new_task->name.'»',
                            'additional_info' => ' Ссылка на задачу: ',
                            'url' => $new_task->task_route(),
                            'task_id' => $new_task->id,
                            'contractor_id' => $new_task->project_id ? Project::find($new_task->project_id)->contractor_id : null,
                            'project_id' => $new_task->project_id ? $new_task->project_id : null,
                            'object_id' => $new_task->project_id ? Project::find($new_task->project_id)->object_id : null,
                        ];
                    } else {
                        Task::where('project_id', $project->id)->where('target_id', $com_offer->id)->where('is_solved', 0)->where('id', '!=', $task_id)->update(['description' => $request->final_note ? 'Комментарий от '.Auth::user()->full_name.': '.$request->final_note : '']);
                    }

                    $usersToNotifyAboutAcceptedCommercialOffer = User::whereIn('group_id', [2])->where('status', '=', 1)->where('is_deleted', '=', 0)->get();
                    if ($project) {
                        $notificationProjectObject = ProjectObject::find($project->object_id);
                        $notificationText = 'Коммерческое предложение согласовано.'.PHP_EOL.'Адрес: '.$notificationProjectObject->address.PHP_EOL;

                        $prepareNotifications['App\Notifications\CommercialOffer\CommercialOfferApprovedNotice'] = [
                            'user_ids' => $usersToNotifyAboutAcceptedCommercialOffer->pluck('id')->toArray(),
                            'name' => $notificationText,
                            'additional_info' => 'Коммерческое предложение: ',
                            'url' => route('projects::commercial_offer'.
                                (($com_offer->is_tongue) ? '::card_tongue' : '::card_pile'),
                                [$com_offer->project_id, $com_offer->id]),
                        ];
                    }
                }
            } elseif ($request->status_result == 'decline') {
                $task->result = 2;
                $task->final_note = (is_null($request->final_note) ? $task->descriptions[$task->status].$task->results[$task->status][$task->result] : $request->final_note);
                $task->save();

                $declined_task = $task;

                if ($com_offer->is_tongue == 1) {
                    $offers_id = CommercialOffer::where('project_id', $task->project_id)->where('id', $task->target_id)->where('is_tongue', 1)->pluck('id')->toArray();

                    $tasks = Task::where('project_id', $task->project_id)->whereIn('status', [5, 6, 12, 15, 16])->whereIn('target_id', $offers_id)->where('is_solved', 0)->get();
                    foreach ($tasks as $item) {
                        $item->result = 2;
                        $item->solve_n_notify();
                    }

                    $com_offer->status = 3;
                    $com_offer->save();

                    $commercial_offer = new CommercialOffer();

                    $commercial_offer->name = 'Коммерческое предложение (шпунтовое направление)';
                    $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 2)->first()->user_id;
                    $commercial_offer->project_id = $project->id;
                    $commercial_offer->work_volume_id = $com_offer->work_volume_id;
                    $commercial_offer->status = 1;
                    $commercial_offer->version = $com_offer->version + 1;
                    $commercial_offer->option = $com_offer->option;
                    $commercial_offer->file_name = 0;
                    $commercial_offer->is_tongue = 1;

                    $commercial_offer->save();
                    $commercial_offer->clone_reviews_from($com_offer);

                    foreach ($com_offer->works as $work) {
                        $work->reviews()->where('result_status', 1)->delete();
                    }

                    foreach (['notes', 'advancements', 'requirements'] as $relation) {
                        foreach ($com_offer->$relation as $item) {
                            $new_item = $item->replicate();
                            $new_item->commercial_offer_id = $commercial_offer->id;
                            $new_item->save();
                            $new_item->clone_reviews_from($item);
                            $new_item->push();
                        }
                    }

                    $splits = CommercialOfferMaterialSplit::where('com_offer_id', $com_offer->id)->get();
                    $splits = (new SplitService())->fixParentChildRelations($splits);

                    foreach ($splits->where('parent_id', null) as $mat_split_old) {
                        $mat_split_copy = $mat_split_old->replicate();
                        $mat_split_copy->com_offer_id = $commercial_offer->id;

                        $subcontractor_file = $mat_split_old->subcontractor_file;
                        if ($subcontractor_file) {
                            $file_copy = $subcontractor_file->replicate();
                            $file_copy->commercial_offer_id = $commercial_offer->id;
                            $file_copy->save();

                            $mat_split_copy->subcontractor_file_id = $file_copy->id;
                        }
                        $mat_split_copy->save();
                        $mat_split_copy->clone_reviews_from($mat_split_old);
                        $mat_split_copy->push();
                        $replChildren = function ($old_parent, $new_parent) use (&$replChildren, $commercial_offer) {
                            foreach ($old_parent->children as $child) {
                                if ($child->com_offer_id != $old_parent->com_offer_id) {
                                    continue;
                                }
                                $child_copy = $child->replicate();
                                $child_copy->com_offer_id = $commercial_offer->id;
                                $child_copy->parent_id = $new_parent->id;

                                $subcontractor_file = $child->subcontractor_file;
                                if ($subcontractor_file) {
                                    $file_copy = $subcontractor_file->replicate();
                                    $file_copy->commercial_offer_id = $commercial_offer->id;
                                    $file_copy->save();

                                    $child_copy->subcontractor_file_id = $file_copy->id;
                                }
                                $child_copy->save();
                                $child_copy->clone_reviews_from($child);
                                $child_copy->push();
                                $replChildren($child, $child_copy);
                            }
                        };
                        $replChildren($mat_split_old, $mat_split_copy);
                    }

                    if ($com_offer->commercial_offer_works()->count()) {
                        foreach ($com_offer->works as $work) {
                            $new_work = $work->replicate();
                            $new_work->commercial_offer_id = $commercial_offer->id;
                            $new_work->save();
                            $new_work->clone_reviews_from($work);
                            $new_work->push();
                        }
                    } else {
                        foreach ($commercial_offer->work_volume->raw_works as $work) {
                            $new_work = CommercialOfferWork::create([
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
                            $new_work->clone_reviews_from($work);
                            $new_work->push();
                        }
                    }

                    $com_offer_request = new CommercialOfferRequest();

                    $com_offer_request->user_id = Auth::id();
                    $com_offer_request->project_id = $project->id;
                    $com_offer_request->commercial_offer_id = $commercial_offer->id;
                    $com_offer_request->status = 0;
                    $com_offer_request->description = $request->final_note;
                    $com_offer_request->is_tongue = 1;

                    $com_offer_request->save();

                    $task_2 = new Task([
                        'project_id' => $project->id,
                        'name' => 'Формирование КП (шпунтовое направление)',
                        'responsible_user_id' => ProjectResponsibleUser::where('project_id', $commercial_offer->project_id)->where('role', 2)->first()->user_id,
                        'contractor_id' => $project->contractor_id,
                        'target_id' => $commercial_offer->id,
                        'prev_task_id' => $declined_task->id,
                        'expired_at' => $this->addHours(24),
                        'status' => 5,
                    ]);

                    $task_2->save();

                    $prepareNotifications['App\Notifications\CommercialOffer\OfferCreationSheetPilingTaskNotice'] = [
                        'user_ids' => $task_2->responsible_user_id,
                        'name' => 'Новая задача «'.$task_2->name.'»',
                        'additional_info' => ' Ссылка на задачу: ',
                        'url' => $task_2->task_route(),
                        'task_id' => $task_2->id,
                        'contractor_id' => $task_2->project_id ? Project::find($task_2->project_id)->contractor_id : null,
                        'project_id' => $task_2->project_id ? $task_2->project_id : null,
                        'object_id' => $task_2->project_id ? Project::find($task_2->project_id)->object_id : null,
                    ];
                } elseif ($com_offer->is_tongue == 0) {
                    $tasks = Task::where('project_id', $project->id)->where('status', 16)->where('target_id', $com_offer->id)->where('is_solved', 0)->get();
                    foreach ($tasks as $item) {
                        $item->result = 2;
                        $item->solve_n_notify();
                    }

                    $com_offer->status = 3;
                    $com_offer->save();

                    $commercial_offer = new CommercialOffer();

                    $commercial_offer->name = 'Коммерческое предложение (свайное направление)';
                    $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 1)->first()->user_id;
                    $commercial_offer->project_id = $project->id;
                    $commercial_offer->work_volume_id = $com_offer->work_volume_id;
                    $commercial_offer->status = 1;
                    $commercial_offer->version = $com_offer->version + 1;
                    $commercial_offer->option = $com_offer->option;
                    $commercial_offer->file_name = 0;
                    $commercial_offer->is_tongue = 0;

                    $commercial_offer->save();
                    $commercial_offer->clone_reviews_from($com_offer);

                    foreach ($com_offer->works as $work) {
                        $work->reviews()->where('result_status', 1)->delete();
                    }

                    foreach (['notes', 'advancements', 'requirements'] as $relation) {
                        foreach ($com_offer->$relation as $item) {
                            $new_item = $item->replicate();
                            $new_item->commercial_offer_id = $commercial_offer->id;
                            $new_item->save();
                            $new_item->clone_reviews_from($item);
                            $new_item->push();
                        }
                    }

                    $splits = CommercialOfferMaterialSplit::where('com_offer_id', $com_offer->id)->get();
                    $splits = (new SplitService())->fixParentChildRelations($splits);

                    foreach ($splits->where('parent_id', null) as $mat_split_old) {
                        $mat_split_copy = $mat_split_old->replicate();
                        $mat_split_copy->com_offer_id = $commercial_offer->id;

                        $subcontractor_file = $mat_split_old->subcontractor_file;
                        if ($subcontractor_file) {
                            $file_copy = $subcontractor_file->replicate();
                            $file_copy->commercial_offer_id = $commercial_offer->id;
                            $file_copy->save();

                            $mat_split_copy->subcontractor_file_id = $file_copy->id;
                        }
                        $mat_split_copy->save();
                        $mat_split_copy->clone_reviews_from($mat_split_old);
                        $mat_split_copy->push();
                        foreach ($mat_split_old->children as $child) {
                            $child_copy = $child->replicate();
                            $child_copy->com_offer_id = $commercial_offer->id;
                            $child_copy->parent_id = $mat_split_copy->id;

                            $subcontractor_file = $child->subcontractor_file;
                            if ($subcontractor_file) {
                                $file_copy = $subcontractor_file->replicate();
                                $file_copy->commercial_offer_id = $commercial_offer->id;
                                $file_copy->save();

                                $child_copy->subcontractor_file_id = $file_copy->id;
                            }
                            $child_copy->save();
                            $child_copy->clone_reviews_from($child);
                            $child_copy->push();
                        }
                    }

                    if ($com_offer->commercial_offer_works()->count()) {
                        foreach ($com_offer->works as $work) {
                            $new_work = $work->replicate();
                            $new_work->commercial_offer_id = $commercial_offer->id;
                            $new_work->save();
                            $new_work->clone_reviews_from($work);
                            $new_work->push();
                        }
                    } else {
                        foreach ($commercial_offer->work_volume->raw_works as $work) {
                            $new_work = CommercialOfferWork::create([
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
                            $new_work->clone_reviews_from($work);
                            $new_work->push();
                        }
                    }

                    $com_offer_request = new CommercialOfferRequest();

                    $com_offer_request->user_id = Auth::id();
                    $com_offer_request->project_id = $project->id;
                    $com_offer_request->commercial_offer_id = $commercial_offer->id;
                    $com_offer_request->status = 0;
                    $com_offer_request->description = $request->final_note;
                    $com_offer_request->is_tongue = 0;

                    $com_offer_request->save();

                    $task_2 = new Task([
                        'project_id' => $project->id,
                        'name' => 'Формирование КП (свайное направление)',
                        'responsible_user_id' => ProjectResponsibleUser::where('project_id', $commercial_offer->project_id)->where('role', 1)->first()->user_id,
                        'contractor_id' => $project->contractor_id,
                        'target_id' => $commercial_offer->id,
                        'prev_task_id' => $declined_task->id,
                        'expired_at' => $this->addHours(24),
                        'status' => 5,
                    ]);

                    $task_2->save();

                    $prepareNotifications['App\Notifications\CommercialOffer\OfferCreationPilingDirectionTaskNotice'] = [
                        'user_ids' => $task_2->responsible_user_id,
                        'name' => 'Новая задача «'.$task_2->name.'»',
                        'task_id' => $task_2->id,
                        'contractor_id' => $task_2->project_id ? Project::find($task_2->project_id)->contractor_id : null,
                        'project_id' => $task_2->project_id ? $task_2->project_id : null,
                        'object_id' => $task_2->project_id ? Project::find($task_2->project_id)->object_id : null,
                    ];
                }
            } elseif ($request->status_result == 'close') {
                $com_offer->status = 3;
                $com_offer->save();

                $task->result = 2;
                $task->final_note = (is_null($request->final_note) ? $task->descriptions[$task->status].$task->results[$task->status][$task->result] : $request->final_note);
                $task->save();
            }

            $task->refresh();
        } elseif ($task->status === 6) {
            $com_offer = CommercialOffer::findOrFail($task->target_id);

            if ($request->status_result === 'accept') {
                $task->result = 1;
                $task->final_note = $task->descriptions[$task->status].$task->results[$task->status][$task->result];
                $task->save();

                $prev_task = $task;
                $com_offer->status = 4;

                if ($project->respUsers()->where('role', ($com_offer->is_tongue ? 6 : 5))->count() == 0) {
                    if ($com_offer->is_tongue) {
                        $main_engineer = Group::find(8)->getUsers()->first();

                        $add_RP_task = Task::create([
                            'project_id' => $project->id,
                            'name' => 'Назначение ответственного руководителя проектов'.($com_offer->is_tongue ? ' (шпунт)' : ' (сваи)'),
                            'responsible_user_id' => $main_engineer ? $main_engineer->id : 6,
                            'contractor_id' => $project->contractor_id,
                            'target_id' => $com_offer->id,
                            'prev_task_id' => $prev_task->id,
                            'status' => $com_offer->is_tongue ? 25 : 24,
                            'expired_at' => $this->addHours(11),
                        ]);

                        $prepareNotifications['App\Notifications\Task\ProjectLeaderAppointmentTaskNotice'] = [
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

                $project = Project::findOrFail($com_offer->project_id);

                $task_created = false;
                if ($project->status < 4 || $project->status == 5) {
                    $project->update(['status' => 4]);

                    foreach (ProjectResponsibleUser::where('project_id', $project->id)->where('role', 7)->get() as $user) {
                        $new_task = new Task([
                            'project_id' => $project->id,
                            'name' => 'Формирование договоров',
                            'responsible_user_id' => $user->user_id,
                            'description' => 'Коммерческое предложение было одобрено, появилась возможность создавать договора.',
                            'prev_task_id' => $prev_task->id,
                            'contractor_id' => $project->contractor_id,
                            'expired_at' => $this->addHours(48),
                            'target_id' => $com_offer->id,
                            'status' => 12,
                        ]);

                        $new_task->save();

                        $prepareNotifications['App\Notifications\Task\ContractCreationTaskNotice'] = [
                            'user_ids' => $new_task->responsible_user_id,
                            'name' => 'Новая задача «'.$new_task->name.'»',
                            'additional_info' => ' Ссылка на задачу: ',
                            'url' => $task->task_route(),
                            'task_id' => $task->id,
                            'contractor_id' => $new_task->project_id ? Project::find($new_task->project_id)->contractor_id : null,
                            'project_id' => $new_task->project_id ? $new_task->project_id : null,
                            'object_id' => $new_task->project_id ? Project::find($new_task->project_id)->object_id : null,
                        ];

                        $task_created = true;
                    }
                }

                if (! $task_created) {
                    foreach (ProjectResponsibleUser::where('project_id', $project->id)->where('role', 7)->get() as $user) {
                        $new_task = new Task([
                            'project_id' => $project->id,
                            'name' => 'Контроль изменений коммерческого предложения',
                            'responsible_user_id' => $user->user_id,
                            'description' => 'Была одобрена новая версия коммерческого предложения. Вы можете ознакомиться с изменениями.',
                            'contractor_id' => $project->contractor_id,
                            'prev_task_id' => $prev_task->id,
                            'expired_at' => $this->addHours(48),
                            'target_id' => $com_offer->id,
                            'status' => 12,
                        ]);

                        $new_task->save();

                        $prepareNotifications['App\Notifications\Task\OfferChangeControlTaskNotice'] = [
                            'user_ids' => $new_task->responsible_user_id,
                            'name' => 'Новая задача «'.$new_task->name.'»',
                            'additional_info' => ' Ссылка на задачу: ',
                            'url' => $task->task_route(),
                            'task_id' => $new_task->id,
                            'contractor_id' => $new_task->project_id ? Project::find($new_task->project_id)->contractor_id : null,
                            'project_id' => $new_task->project_id ? $new_task->project_id : null,
                            'object_id' => $new_task->project_id ? Project::find($new_task->project_id)->object_id : null,
                        ];
                    }
                }
            } elseif ($request->status_result == 'archive') {
                $com_offer->status = 3;
                $task->result = 2;
                $task->final_note = (is_null($request->final_note) ? $task->descriptions[$task->status].$task->results[$task->status][$task->result] : $request->final_note);
                $task->save();

                Project::where('id', $com_offer->project_id)->update(['status' => 5]);
            } elseif ($request->status_result == 'transfer') {
                $task->result = 3;
                $task->revive_at = Carbon::parse($request->revive_at);
                $task->final_note = (is_null($request->final_note) ? $task->descriptions[$task->status].$task->results[$task->status][$task->result].$request->revive_at : $request->final_note);
                $task->is_solved = 1;

                $task->save();
            } elseif ($request->status_result == 'change') {
                $task->result = 4;
                $task->final_note = $task->descriptions[$task->status].$task->results[$task->status][$task->result];
                $task->save();

                $com_offer = CommercialOffer::findOrFail($task->target_id);
                $project = Project::findOrFail($com_offer->project_id);

                if ($com_offer->is_tongue == 1) {
                    $offers_id = CommercialOffer::where('project_id', $task->project_id)->where('id', $task->target_id)->where('is_tongue', 1)->pluck('id')->toArray();
                    $com_offer->status = 3;
                    $com_offer->save();

                    $tasks = Task::where('project_id', $task->project_id)->whereIn('status', [5, 6, 12, 15, 16])->whereIn('target_id', $offers_id)->where('is_solved', 0)->get();
                    foreach ($tasks as $item) {
                        $item->result = 4;
                        $item->solve_n_notify();
                    }

                    $commercial_offer = new CommercialOffer();

                    $commercial_offer->name = 'Коммерческое предложение (шпунтовое направление)';
                    $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 2)->first()->user_id;
                    $commercial_offer->project_id = $project->id;
                    $commercial_offer->work_volume_id = $com_offer->work_volume_id;
                    $commercial_offer->status = 1;
                    $commercial_offer->version = $com_offer->version + 1;
                    $commercial_offer->option = $com_offer->option;
                    $commercial_offer->file_name = 0;
                    $commercial_offer->is_tongue = 1;
                    $commercial_offer->is_uploaded = $com_offer->is_uploaded;

                    $commercial_offer->save();

                    if ($com_offer) {
                        foreach ($com_offer->notes as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        foreach ($com_offer->requirements as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        foreach ($com_offer->advancements as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        $splits = CommercialOfferMaterialSplit::where('com_offer_id', $com_offer->id)->get();

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
                    }

                    $com_offer_request = new CommercialOfferRequest();

                    $com_offer_request->user_id = Auth::id();
                    $com_offer_request->project_id = $project->id;
                    $com_offer_request->commercial_offer_id = $commercial_offer->id;
                    $com_offer_request->status = 0;
                    $com_offer_request->description = $request->final_note;
                    $com_offer_request->is_tongue = 1;

                    $com_offer_request->save();

                    $task_2 = new Task([
                        'project_id' => $project->id,
                        'name' => 'Формирование КП (шпунтовое направление)',
                        'responsible_user_id' => ProjectResponsibleUser::where('project_id', $commercial_offer->project_id)->where('role', 2)->first()->user_id,
                        'contractor_id' => $project->contractor_id,
                        'target_id' => $commercial_offer->id,
                        'prev_task_id' => $com_offer->tasks->last()->id,
                        'expired_at' => $this->addHours(24),
                        'status' => 5,
                    ]);

                    $task_2->save();

                    $prepareNotifications['App\Notifications\CommercialOffer\OfferCreationSheetPilingTaskNotice'] = [
                        'user_ids' => $task_2->responsible_user_id,
                        'name' => 'Новая задача «'.$task_2->name.'»',
                        'additional_info' => ' Ссылка на задачу: ',
                        'url' => $task_2->task_route(),
                        'task_id' => $task_2->id,
                        'contractor_id' => $task_2->project_id ? Project::find($task_2->project_id)->contractor_id : null,
                        'project_id' => $task_2->project_id ? $task_2->project_id : null,
                        'object_id' => $task_2->project_id ? Project::find($task_2->project_id)->object_id : null,
                    ];
                } elseif ($com_offer->is_tongue == 0) {

                    $com_offer->status = 3;
                    $com_offer->save();

                    $tasks = Task::where('project_id', $project->id)->where('status', 16)->where('target_id', $com_offer->id)->where('is_solved', 0)->get();
                    foreach ($tasks as $item) {
                        $item->result = 4;
                        $item->solve_n_notify();
                    }

                    $commercial_offer = new CommercialOffer();

                    $commercial_offer->name = 'Коммерческое предложение (свайное направление)';
                    $commercial_offer->user_id = ProjectResponsibleUser::where('project_id', $project->id)->where('role', 1)->first()->user_id;
                    $commercial_offer->project_id = $project->id;
                    $commercial_offer->work_volume_id = WorkVolume::where('project_id', $project->id)->where('status', 2)->where('type', 1)->first()->id;
                    $commercial_offer->status = 1;
                    $commercial_offer->version = $com_offer->version + 1;
                    $commercial_offer->option = $com_offer->option;
                    $commercial_offer->file_name = 0;
                    $commercial_offer->is_tongue = 0;
                    $commercial_offer->is_uploaded = $com_offer->is_uploaded;

                    $commercial_offer->save();

                    if ($com_offer) {
                        foreach ($com_offer->notes as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        foreach ($com_offer->requirements as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        foreach ($com_offer->advancements as $item) {
                            $new_note = $item->replicate();
                            $new_note->commercial_offer_id = $commercial_offer->id;
                            $new_note->save();
                        }

                        $splits = CommercialOfferMaterialSplit::where('com_offer_id', $com_offer->id)->get();

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
                    }

                    $com_offer_request = new CommercialOfferRequest();

                    $com_offer_request->user_id = Auth::id();
                    $com_offer_request->project_id = $project->id;
                    $com_offer_request->commercial_offer_id = $commercial_offer->id;
                    $com_offer_request->status = 0;
                    $com_offer_request->description = $request->final_note;
                    $com_offer_request->is_tongue = 0;

                    $com_offer_request->save();

                    $task_2 = new Task([
                        'project_id' => $project->id,
                        'name' => 'Формирование КП (свайное направление)',
                        'responsible_user_id' => ProjectResponsibleUser::where('project_id', $commercial_offer->project_id)->where('role', 1)->first()->user_id,
                        'contractor_id' => $project->contractor_id,
                        'target_id' => $commercial_offer->id,
                        'prev_task_id' => $com_offer->tasks->last()->id,
                        'expired_at' => $this->addHours(24),
                        'status' => 5,
                    ]);

                    $task_2->save();

                    $prepareNotifications['App\Notifications\CommercialOffer\OfferCreationPilingDirectionTaskNotice'] = [
                        'user_ids' => $task_2->responsible_user_id,
                        'name' => 'Новая задача «'.$task_2->name.'»',
                        'task_id' => $task_2->id,
                        'contractor_id' => $task_2->project_id ? Project::find($task_2->project_id)->contractor_id : null,
                        'project_id' => $task_2->project_id ? $task_2->project_id : null,
                        'object_id' => $task_2->project_id ? Project::find($task_2->project_id)->object_id : null,
                    ];
                }
            }

            $status_for_humans = [
                'accept' => 'Принято',
                'archive' => 'В архив',
                'change' => 'Требуются изменения',
                'transfer' => 'Перенесено',
            ];

            $task->refresh();

            $name = 'Задача «'.$task->name.'» закрыта с результатом: '.$status_for_humans[$request->status_result].
                    (is_null($task->revive_at) ? '' : '. Дата, на которую перенесли: '
                        .strftime('%d.%m.%Y', strtotime($task->revive_at))).(is_null($task->final_note) ? ''
                    : '. Комментарий: '.$task->final_note);

            $prepareNotifications['App\Notifications\Task\TaskClosureNotice'] = [
                'user_ids' => Group::find(5/*3*/)->getUsers()->first()->id,
                'name' => $name,
                'additional_info' => "\r\nЗаказчик: ".Project::find($task->project_id)->contractor_name.
                    "\r\nНазвание объекта: ".Project::find($task->project_id)->object->name.
                    "\r\nАдрес объекта: ".Project::find($task->project_id)->object->address.
                    "\r\n Исполнитель: ".User::find($task->responsible_user_id)->long_full_name,
                'url' => route('projects::card', [$task->project_id, 'task' => $task->id]),
                'task_id' => $task->id,
                'status' => 2,
                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
            ];

            $com_offer->save();

        } elseif ($task->status == 20) {
            $contract = Contract::findOrFail($task->target_id);
            $task->result = $request->status_result == 'accept' ? 1 : 2;
            $task->final_note = $task->descriptions[$task->status].$task->results[$task->status][$task->result].
                ($request->description ? ', с комментарием: '.$request->description : '');
            $task->solve_n_notify();

            $prepareNotifications['App\Notifications\Contract\ContractDeletionRequestResolutionNotice'] = [
                'user_ids' => $task->user_id,
                'name' => 'Запрашиваемый вами договор '.$contract->name_for_humans.
                    ' '.$task->results[$task->status][$task->result].
                    ($request->description ? ', комментарий: '.$request->description : ''),
                'task_id' => $task->id,
                'contractor_id' => $task->contractor_id,
            ];

            if ($request->status_result == 'accept') {
                // remove relations and contract, solve tasks
                $contract_files = ContractFiles::where('contract_id', $task->target_id)->delete();
                $contract_request = ContractRequest::where('contract_id', $task->target_id);
                $contract_request_files = ContractRequestFile::whereIn('request_id', $contract_request->pluck('id')->toArray())->delete();
                $contract_request->delete();
                $contract_theses = ContractThesis::where('contract_id', $task->target_id);
                $contract_theses_verifiers = ContractThesisVerifier::whereIn('thesis_id', $contract_theses->pluck('id')->toArray())->delete();
                $contract_theses->delete();
                $contract->load('tasks');

                foreach ($contract->tasks as $task) {
                    $task->solve_n_notify();
                }

                $contract->delete();
            }
        }
        //в эту функцию также приходит задача со статусом 12, 6, она просто закрывается.
        if ($request->status_result != 'transfer') {
            $task->solve_n_notify();
        }

        DB::commit();

        foreach ($prepareNotifications as $class => $arguments) {
            try {
                $user_id = $arguments['user_ids'];
                $class::send(
                    $user_id,
                    $arguments
                );
            } catch (\Throwable $throwable) {
                $message = "В контроллере TaskCommerceController, не удалось отправить уведомление $class, возникла ошибка: ";
                Log::error($message.$throwable->getMessage());
            }
        }

        if ($request->has('where_from')) {
            return back();
        } else {
            return redirect()->route('tasks::index');
        }
    }

    public function postpone(Request $request, $task_id)
    {
        DB::beginTransaction();
        $task = Task::find($task_id);
        $task->revive_at = Carbon::parse(str_replace('/', '.', $request->revive_at));
        $task->description = $request->description."\n Задача была перенесена на "."$request->revive_at";
        $task->final_note = $request->description;
        $task->is_solved = 1;

        $task->save();

        DB::commit();

        TaskPostponedAndClosedNotice::send(
            $task->responsible_user_id,
            [
                'name' => 'Задача «'.$task->name.'» отложена и закрыта',
                'task_id' => $task->id,
                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
            ]
        );

        return redirect(route('tasks::index'));
    }

    public function declineRequest(Request $request)
    {
        DB::beginTransaction();

        $task = Task::find($request->task_id);
        if ($task->is_solved == 0) {
            $task->result = 2;
            $task->final_note = $task->descriptions[$task->status].Auth::user()->full_name.$task->results[$task->status][$task->result];
            $task->is_solved = 1;
            $task->save();
        }

        DB::commit();

        return response()->json(true);
    }

    public function slimTask($id)
    {
        $task = Task::findOrFail($id);
        $task->load('contractor', 'project', 'changing_fields');

        return view('tasks.slim_task', [
            'task' => $task,
            'operation' => $task->status == 45 ? MaterialAccountingOperation::find($task->target_id) : false,
        ]);
    }
}
