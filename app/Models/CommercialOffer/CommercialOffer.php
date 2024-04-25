<?php

namespace App\Models\CommercialOffer;

use App\Domain\Enum\NotificationType;
use App\Models\Company\Company;
use App\Models\Contract\Contract;
use App\Models\Contractors\{Contractor, ContractorContact};
use App\Models\FileEntry;
use App\Models\Manual\ManualWork;
use App\Models\Notification\Notification;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\ProjectResponsibleUser;
use App\Models\Review;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Models\WorkVolume\WorkVolumeWorkMaterial;
use App\Services\Commerce\SplitService;
use App\Traits\Commentable;
use App\Traits\Reviewable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Image;
use PDF;
use phpDocumentor\Reflection\Types\Integer;

class CommercialOffer extends Model
{
    use Commentable, Reviewable, SoftDeletes;


    public $com_offer_status = [
        1 => 'В работе',
        2 => 'На согласовании',
        3 => 'Отклонено',
        4 => 'Согласовано с заказчиком',
        5 => 'Отправлено'
    ];
    public $com_offer_status_description = [
        1 => 'Ведутся работы по формированию коммерческого предложения',
        2 => 'Внутреннее согласование с Генеральным директором или его заместителем',
        3 => 'Было отклонено в ходе внутренного согласования или заказчиком',
        4 => 'Заказчик принял данный вариант КП',
        5 => 'Сформированное КП было отправлено на рассмотрение заказчику'
    ];

    protected $fillable = ['name', 'user_id', 'file_name', 'project_id', 'version', 'status', 'work_volume_id', 'contract_number', 'contract_date', 'is_tongue', 'title', 'is_uploaded', 'option'];

    protected $appends = ['status_description', 'type_name', 'status_name'];
    /** Statuses for coloring in situation
     * when CO project is important
     */
    const NICE_STATUSES = [1, 2, 5];

    public function getStatusDescriptionAttribute()
    {
        return $this->com_offer_status_description[$this->status];
    }

    public function getTypeNameAttribute()
    {
        return $this->work_volume->type_name;
    }

    public function getStatusNameAttribute()
    {
        return $this->com_offer_status[$this->status];
    }
    /**
     * Scope a query commercial offers
     * at documents block.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDocuments($query)
    {
        return $query->with('project')
        ->leftjoin('users', 'users.id', '=', 'commercial_offers.user_id')
        ->leftjoin('projects', 'projects.id', '=', 'commercial_offers.project_id')
        ->leftjoin('contractors', 'contractors.id', '=', 'projects.contractor_id')
        ->leftjoin('project_objects', 'project_objects.id', '=', 'projects.object_id')
        ->whereIn('commercial_offers.id', [DB::raw('select max(commercial_offers.id) from commercial_offers GROUP BY project_id, `option`')])
        ->select('commercial_offers.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'),
            'projects.name as project_name', 'projects.entity as project_entity', 'contractors.short_name as contractor_name',
            'contractors.id as contractor_id', 'project_objects.address as address', 'projects.is_important as is_important');
    }

    /**
     * Function check that commercial offer
     * is in agreed with customer status
     * @return bool
     */
    public function isAgreedWithCustomer(): bool
    {
        return $this->status == 4;
    }

    /**
     * Function check that commercial offer
     * is not in agreed with customer status
     * @return bool
     */
    public function isNotAgreedWithCustomer(): bool
    {
        return ! $this->isAgreedWithCustomer();
    }

    /**
     * Function check that commercial offer
     * is in NICE_STATUSES status and
     * CO project is important
     * @return bool
     */
    public function isNeedToBeColored(): bool
    {
        return boolval(($this->project->is_important ?? false) and in_array($this->status, self::NICE_STATUSES));
    }

    /**
     * Overrides function for Com Offer only.
     * Com offer has no reviews itself.
     * This is for those reviews that don't have own model in project
     *
     * @param  integer|string|null $type Type of the category review you want to get (null for both, 1 for material, 2 for works)
     * @param  int|null $reviewable_id Index of material or work
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews($type = null, $reviewable_id = null)
    {
        if(is_null($type) and is_null($reviewable_id)) {
            return $this->hasMany(Review::class, 'commercial_offer_id', 'id')->whereIn('reviewable_type', ['MaterialWorkRelation', 'App\Models\Manual\ManualWork']);
        } elseif($type == '1') {
            return $this->hasMany(Review::class, 'commercial_offer_id', 'id')->where('reviewable_id', $reviewable_id)->where('reviewable_type', 'MaterialWorkRelation');
        } elseif($type == '2') {
            return $this->hasMany(Review::class, 'commercial_offer_id', 'id')->where('reviewable_id', $reviewable_id)->where('reviewable_type', 'App\Models\Manual\ManualWork');
        }
        return collect();
    }

    public function clone_reviews_from($old_owner)
    {
        foreach($old_owner->reviews()->where('result_status', 0)->get() as $review) {
            $new_review = $review->replicate();
            $new_review->commercial_offer_id = $this->id;
            $new_review->save();
        }
        return true;
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

//methods
    public function decline()
    {
        $project = Project::findOrFail($this->project_id);

        $this->unsolved_tasks->each(function ($task) use($project) {
            dispatchNotify(
                $task->responsible_user_id,
                'Задача «' . $task->name . '» закрыта',
                '',
                NotificationType::TASK_CLOSURE_NOTIFICATION,
                [
                    'task_id' => $task->id,
                    'contractor_id' => $project->contractor_id,
                    'project_id' => $project->id,
                    'object_id' => $project->object_id,
                ]
            );

            $task->solve();
        });

        $this->get_requests()->update(['status' => 1]);

        $this->update(['status' => 3]);
        $this->save();
    }


    public function getAdaptedSplitsAttribute()
    {
        $split_wv_mat = $this->mat_splits->sortBy('type')->groupBy(['WV_material.manual_material_id','type']);

        foreach ($split_wv_mat as $id => $material) {
            foreach ($material as $type => $split) {
                if (in_array($type, [3, 4])) {
                    $split_wv_mat[$id][$type] = $split->groupBy('time');

                    foreach ($split->groupBy('time') as $time => $mat) {
                        if ($type == 4 and isset($split_wv_mat[$id][3 . '|' . $time])) {
                            $split_wv_mat[$id]['3' . '|' . $time] = [
                                'security' => $mat->sum('count'),
                                'count' => $split_wv_mat[$id]['3' . '|' . $time]['count'],
                                'is_hidden' => $split_wv_mat[$id]['3' . '|' . $time]['is_hidden'],
                                'id' => $split_wv_mat[$id]['3' . '|' . $time]['id'],
                            ];
                        } else {
                            $split_wv_mat[$id][$type . '|' . $time] = [
                                'count' => $mat->sum('count'),
                                'is_hidden' => $mat[0]->is_hidden,
                                'id' => $mat[0]->id,
                            ];
                        }
                    }
                    $split_wv_mat[$id]->forget($type);

                } elseif ($type == 2) {
                    $split_wv_mat[$id][1] = [
                        'buyback' => $split->sum('count'),
                        'count' => $split_wv_mat[$id][1]['count'],
                        'is_hidden' => $split_wv_mat[$id][1]['is_hidden'],
                        'id' => $split_wv_mat[$id][1]['id'],
                    ];
                    $split_wv_mat[$id]->forget($type);
                } else {
                    $split_wv_mat[$id][$type] = [
                        'count' => $split->sum('count'),
                        'is_hidden' => $split[0]->is_hidden,
                        'id' => $split[0]->id,
                    ];
                }
            }
        }

        return $split_wv_mat;
    }

    /**
     * Transfer com offer to status 2.
     * makes all necessary tasks, closes others
     *
     * @return Collection
     * there are notifications to send after DB commit in this collection.
     * be sure to merge them with your local events_cache <- but
     * now with new notification backend we don't need to do this
     */

    public function to_negotiation()
    {
        $this->status = 2;
        $project = Project::findOrFail($this->project_id);

        if ($this->is_tongue == 1) {
            foreach ([5, 6] as $group_id) {
                $old_tasks = Task::where('project_id', $this->project_id)->where('status', 5)->where('target_id', $this->id)->where('is_solved', 0)->get();

                foreach ($old_tasks as $old_task) {
                    $old_task->solve_n_notify();
                }

                $task = new Task([
                    'project_id' => $this->project_id,
                    'name' => 'Согласование КП (шпунт)',
                    'responsible_user_id' => User::where('group_id', $group_id)->first()->id,
                    'contractor_id' => $project->contractor_id,
                    'target_id' => $this->id,
                    'expired_at' => $this->addHours(8),
                    'prev_task_id' => $old_tasks->first()->id ?? null,
                    'status' => 16
                ]);

                $task->save();

                dispatchNotify(
                    $task->responsible_user_id,
                    'Новая задача «' . $task->name . '»',
                    '',
                    NotificationType::APPROVAL_OF_OFFER_SHEET_PILING_TASK_NOTIFICATION,
                    [
                        'additional_info' => "\r\nЗаказчик: " . $project->contractor_name .
                            "\r\nНазвание объекта: " . $project->object->name .
                            "\r\nАдрес объекта: " . $project->object->address,
                        'url' => $task->task_route(),
                        'task_id' => $task->id,
                        'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                        'project_id' => $task->project_id ? $task->project_id : null,
                        'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                    ]
                );
            }
        } else if ($this->is_tongue == 0) {
            foreach ([5, 6] as $group_id) {
                $old_tasks = Task::where('project_id', $this->project_id)->where('status', 5)->where('target_id', $this->id)->where('is_solved', 0)->get();

                foreach ($old_tasks as $old_task) {
                    $old_task->solve_n_notify();
                }

                $task = new Task([
                    'project_id' => $this->project_id,
                    'name' => 'Согласование КП (сваи)',
                    'responsible_user_id' => User::where('group_id', $group_id)->first()->id,
                    'contractor_id' => $project->contractor_id,
                    'target_id' => $this->id,
                    'expired_at' => $this->addHours(8),
                    'prev_task_id' => $old_tasks->first()->id ?? null,
                    'status' => 16
                ]);

                $task->save();

                dispatchNotify(
                    $task->responsible_user_id,
                    'Новая задача «' . $task->name . '»',
                    '',
                    NotificationType::PILE_DRIVING_OFFER_APPROVAL_TASK_CREATION_NOTIFICATION,
                    [
                        'additional_info' => "\r\nЗаказчик: " . $project->contractor_name .
                            "\r\nНазвание объекта: " . $project->object->name .
                            "\r\nАдрес объекта: " . $project->object->address,
                        'url' => $task->task_route(),
                        'task_id' => $task->id,
                        'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                        'project_id' => $task->project_id ? $task->project_id : null,
                        'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                    ]
                );
            }
        }
    }


    public function create_offer_pdf($offer_id, $COtype = 'regular', $from_task = false)
    {
        $offer = CommercialOffer::where('id', $offer_id)
            ->with('notes', 'requirements', 'advancements', 'signer')
            ->firstOrFail();

        $works = $offer->works()->where('is_hidden', 0)->get()->sortBy('order');

        $work_priors = WorkVolume::findOrFail($offer->work_volume_id)->works_offer->where('is_hidden', 0)->where('term', '>', 0);

        DB::beginTransaction();

        $months = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря',
        ];

        $work_volume = WorkVolume::with(['works_offer' => function ($q) {
            $q->where('is_hidden', 0);
        }]) ->where('work_volumes.id', $offer->work_volume_id)
            ->leftJoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->select('work_volumes.*', 'projects.name as project_name', 'projects.contractor_id as contractor_id', 'projects.object_id as object_id')
            ->first();

        $split_wv_mat = collect([]);

        foreach ($split_wv_mat as $id => $material) {

            foreach ($material as $type => $split) {

                if (in_array($type, [3, 4])) {
                    $split_wv_mat[$id][$type] = $split->groupBy('time');

                    foreach ($split->groupBy('time') as $time => $mat) {

                        $split_wv_mat[$id][$type .'|'. $time] = $mat->sum('count');
                    }
                    $split_wv_mat[$id]->forget($type);
                } else {
                    $split_wv_mat[$id][$type] = $split->sum('count');
                }
            }
        }

        $materials_ids = [];
        foreach ($offer->works as $work) {
            if (!is_array($work->relations)) {
                $materials_ids[] = $work->relations->pluck('wv_material_id')->toArray();
            }
        }

        $materials = $work_volume->shown_materials;

        $splits = $offer->mat_splits()->where('type', '!=', 5)->with(['subcontractor_file', 'buyback', 'security'])->get();

        $today = Carbon::now()->format('d-m-Y');

        $resp_users = !$offer->is_tongue ? ProjectResponsibleUser::where('project_id', $offer->project_id)->whereIn('role',[1,3]) : ProjectResponsibleUser::where('project_id', $offer->project_id)->whereIn('role',[2,4]);

        $resp_users->leftJoin('users', 'users.id', '=', 'user_id')
            ->leftJoin('groups', 'groups.id', '=', 'users.group_id')
            ->select('project_responsible_users.*', 'users.first_name', 'users.last_name', 'users.patronymic', 'users.work_phone', 'users.person_phone', 'groups.name as profession');

        $contact = ContractorContact::with('phones')->find($offer->contact_id);
        $commercialOfferProject = Project::findOrFail($offer->project_id);
        $commercialOfferCompany = Company::findOrFail($commercialOfferProject->entity);

        if (isset($contact->phones)) {
            $contact->phone_number = $contact->phones->where('is_main', 1)->count() > 0 ? $contact->phones->where('is_main', 1)->pluck('phone_number')->first() : $contact->phones->pluck('phone_number')->first();
            $contact->dop_phone = $contact->phones->where('is_main', 1)->count() > 0 ? $contact->phones->where('is_main', 1)->pluck('dop_phone')->first() : $contact->phones->pluck('dop_phone')->first();
        }
        if ($COtype == 'regular') {
            $data = [
                'offer' => $offer,
                'works' => $works,
                'resp_users' => $resp_users->get()->unique('user_id'),
                'work_volume' => $work_volume,
                'contractor' => Contractor::findOrFail($work_volume->contractor_id),
                'object' => ProjectObject::findOrFail($work_volume->object_id),
                'today' => $today,
                'materials' => $materials->unique('manual_material_id')->whereIn('manual_material_id', $splits->pluck('man_mat_id')->toArray())->unique(),
                'split_wv_mat' => $split_wv_mat,
                'splits' => $splits,
                'contact' => $contact,
                'project' => $commercialOfferProject,
                'company' => $commercialOfferCompany,
                'work_groups' => (new ManualWork())->work_group,
            ];
        } else {
            $data = [
                'offer' => $offer,
                'works' => $works,
                'resp_users' => $resp_users->get()->unique('user_id'),
                'work_volume' => $work_volume,
                'contractor' => Contractor::findOrFail($work_volume->contractor_id),
                'object' => ProjectObject::findOrFail($work_volume->object_id),
                'today' => $today,
                'materials' => $materials->unique('manual_material_id')->whereIn('manual_material_id', array_keys($splits->pluck('man_mat_id')->toArray()))->unique(),
                'split_wv_mat' => $split_wv_mat,
                'splits' => $splits,
                'contact' => $contact,
                'project' => $commercialOfferProject,
                'company' => $commercialOfferCompany,
                'work_groups' => (new ManualWork())->work_group,
            ];
        }
        $data['rel_size'] = 1; //1.18//0.82

        $set_page_break = [
            'section' => -2,
            'points' => 0,
        ];
        $data['set_page_break'] = $set_page_break;
        $pdf = PDF::loadView('projects.commercial_offer.offer', $data, [], [
            'margin_bottom' => 40
        ]);
//        $pdf_new = PDF::loadView('projects.commercial_offer.offer', $data);
        $pdf_new = $pdf;

        if((count($pdf_new->mpdf->pages) != 1)) {
            $sum_count = 0;
            foreach ($pdf_new->mpdf->pages as $page) {
                $sum_count += strlen(explode('___HEADER___', $page)[1]);
            }
            $average_count = $sum_count / (count($pdf_new->mpdf->pages) - 1);


            if (($average_count < 0.05)) { //if on last page more that 5% of full page (aprox 43k)
                $set_page_break = [
                    'section' => -1,
                    'points' => 0,
                ];
                $requirements_count = $offer->requirements->count();
                if ($requirements_count > 0 and $set_page_break['section'] == -1) {
                    $set_page_break['section'] = 0;
                    if ($requirements_count > 2) {
                        $set_page_break['points'] = 3;
                        $total_note_symbols = 0;
                        foreach ($offer->requirements->slice($offer->requirements->count() - 3)->reverse() as $index => $requirement) {
                            $total_note_symbols += strlen($requirement->requirement);
                            if ($total_note_symbols > 250) {
                                $set_page_break['points'] = $requirements_count - $index;
                                break;
                            }
                        }
                    }
                }

                $notes_count = $offer->notes->count();
                if ($notes_count > 0 and $set_page_break['section'] == -1) {
                    $set_page_break['section'] = 1;
                    if ($notes_count > 2) {
                        $set_page_break['points'] = 3;
                        $total_note_symbols = 0;
                        foreach ($offer->notes->slice($offer->notes->count() - 3)->reverse() as $index => $note) {
                            $total_note_symbols += strlen($note->note);
                            if ($total_note_symbols > 250) {
                                $set_page_break['points'] = $notes_count - $index;
                                break;
                            }
                        }
                    }
                }

                $advancements_count = $offer->advancements->count();
                if ($advancements_count > 0 and $set_page_break['section'] == -1) {
                    $set_page_break['section'] = 2;
                    if ($advancements_count > 2) {
                        $set_page_break['points'] = 3;
                        $total_note_symbols = 0;
                        foreach ($offer->advancements->slice($offer->advancements->count() - 3)->reverse() as $index => $advancement) {
                            $total_note_symbols += strlen($advancement->description);
                            if ($total_note_symbols > 250) {
                                $set_page_break['points'] = $advancements_count - $index;
                                break;
                            }
                        }
                    }
                }

                $data['set_page_break'] = $set_page_break;
                $pdf_new = PDF::loadView('projects.commercial_offer.offer', $data, [], [
                    'margin_bottom' => 40
                ]);
            }
        }
        $file_name = 'project-' . $offer->project_id . '_commercial_offer-' . uniqid() . '.' . 'pdf';

        $filenamePathParts = ['app', 'public', 'docs', 'commercial_offers', $file_name];
        $pdf_new->save(storage_path(implode(DIRECTORY_SEPARATOR, $filenamePathParts)));

        FileEntry::create(['filename' => $file_name, 'size' => 0,
            'mime' => 'pdf', 'original_filename' => $file_name, 'user_id' => Auth::user()->id,]);

        $offer->file_name = $file_name;

        $offer->save();

//        dd($set_page_break);
        DB::commit();
        if ($from_task) {
            return true;
        }
        return $pdf_new->stream('commercial_offer_' . $today . '.pdf');
//        return view('projects.commercial_offer.offer', $data);
    }

    /**
     * makes a replica of Com_offer
     * also copy all necessary relations
     * and even work_volume
     *
     * @param
     * @param
     *
     * @return CommercialOffer
     */

    public function createCopy($project_id, $option = null)
    {
        DB::beginTransaction();

        $target_project = Project::find($project_id);
        $last_com_offer = $target_project->com_offers()->where('is_tongue', $this->is_tongue);
        $exist_offer_option = null;
        if ($option) {
            if(strpos($option, 'id:') === 0) {
                $com_offer_id = substr($option, 3, strlen($option));
                $last_com_offer->where('id', $com_offer_id);
                $option = '';
            } else {
                $last_com_offer->where('option', $option);
                $exist_offer_option = CommercialOffer::whereProjectId($target_project->id)->where('option', $option)->orderBy('id', 'asc')->first();
            }
        }
        $last_com_offer = $last_com_offer->orderByDesc('version')->first();
        $last_com_offer_version = 0;
        if ($last_com_offer) {
            $last_com_offer_version = $last_com_offer->version;
            $last_com_offer->decline();
        }

        $com_offer_old = $this;

        $com_offer_copy = $com_offer_old->replicate();
        $com_offer_copy->save();

        //copy work volume
        $work_volume_old = $this->work_volume;
        $work_volume_copy = $work_volume_old->replicate();
        $work_volume_copy->save();

        $work_volume_copy->project_id = $project_id;
        $work_volume_copy->option = $option;
        if ($exist_offer_option) {
            $work_volume_copy->version = $exist_offer_option->version + 1;

            $exist_offer_option->status = 3;
            $exist_offer_option->save();
        } else {
            $work_volume_copy->version = 1;
        }

        $material_work_relations = collect([]);
        $complect_id_diff = collect([]);

        foreach ($work_volume_old->complect_materials as $complect_old) {
            $complect_copy = $complect_old->replicate();
            $complect_copy->work_volume_id = $work_volume_copy->id;
            $complect_copy->save();

            foreach ($complect_old->works as $work) {
                $material_work_relations->push([
                    'wv_material_id' => $complect_copy->id,
                    'wv_work_id' => $work->id,
                ]);
            }

            $complect_copy_manual = $complect_old->manual->replicate();
            $complect_copy_manual->work_volume_id = $work_volume_copy->id;
            $complect_copy_manual->save();

            $complect_id_diff->push([
                'old_id' => $complect_old->manual->id,
                'new_id' => $complect_copy_manual->id,
            ]);

            $complect_copy_manual->wv_material_id = $complect_copy->id;
            $complect_copy->manual_material_id = $complect_copy_manual->id;

            foreach ($complect_old->parts as $part_old) {
                $part_copy = $part_old->replicate();
                $part_copy->complect_id = $complect_copy->id;
                $part_copy->work_volume_id = $work_volume_copy->id;
                $part_copy->save();

                foreach ($part_old->works as $work) {
                    $material_work_relations->push([
                        'wv_material_id' => $part_copy->id,
                        'wv_work_id' => $work->id,
                    ]);
                }
            }

            $complect_copy->save();
            $complect_copy_manual->save();
        }

        foreach ($work_volume_old->regular_materials as $regular_old) {
            $regular_copy = $regular_old->replicate();
            $regular_copy->work_volume_id = $work_volume_copy->id;
            $regular_copy->save();

            foreach ($regular_old->works as $work) {
                $material_work_relations->push([
                    'wv_material_id' => $regular_copy->id,
                    'wv_work_id' => $work->id,
                ]);
            }
        }

        $manual_wv_id_map = collect();
        foreach ($work_volume_old->raw_works as $work_old) {
            $work_copy = $work_old->replicate();
            $work_copy->work_volume_id = $work_volume_copy->id;

            $subcontractor_file = $work_old->subcontractor_file;
            if ($subcontractor_file) {
                $file_copy = $work_old->subcontractor_file->replicate();
                $file_copy->commercial_offer_id = $com_offer_copy->id;
                $file_copy->save();

                $work_copy->subcontractor_file_id = $file_copy->id;
            }
            $work_copy->save();
            $work_copy->refresh();
            $manual_wv_id_map->push([
                'manual_work_id' => $work_copy->manual_work_id,
                'work_volume_work_id' => $work_copy->id,
            ]);

            $material_work_relations = $material_work_relations->map(function ($relation) use ($work_old, $work_copy) {
                if ($relation['wv_work_id'] == $work_old->id) {
                    $relation['wv_work_id'] = $work_copy->id;
                }
                return $relation;
            });
        }

        foreach ($material_work_relations as $material_work_relation) {
            WorkVolumeWorkMaterial::create($material_work_relation);
        }
        //finish copying work volume

        //start copying commercial offer
        $com_offer_copy->status = 1;
        $com_offer_copy->version = $last_com_offer_version + 1;
        $com_offer_copy->file_name = '';
        $com_offer_copy->project_id = $project_id;
        $com_offer_copy->work_volume_id = $work_volume_copy->id;
        $com_offer_copy->option = $option;

        foreach ($com_offer_old->advancements as $advancement_old) {
            $advancement_copy = $advancement_old->replicate();
            $advancement_copy->commercial_offer_id = $com_offer_copy->id;
            $advancement_copy->save();
        }
        foreach ($com_offer_old->notes as $note_old) {
            $note_copy = $note_old->replicate();
            $note_copy->commercial_offer_id = $com_offer_copy->id;
            $note_copy->save();
        }
        foreach ($com_offer_old->requirements as $requirement_old) {
            $requirement_copy = $requirement_old->replicate();
            $requirement_copy->commercial_offer_id = $com_offer_copy->id;
            $requirement_copy->save();
        }

        $splits = (new SplitService())->fixParentChildRelations($com_offer_old->mat_splits);

        foreach ($splits->where('parent_id', null) as $mat_split_old) {
            $mat_split_copy = $mat_split_old->replicate();
            $mat_split_copy->com_offer_id = $com_offer_copy->id;
            if (count($complect_id_diff->where('old_id', $mat_split_old->man_mat_id))) {
                $mat_split_copy->man_mat_id = $complect_id_diff->where('old_id', $mat_split_old->man_mat_id)->first()['new_id'] ?? $mat_split_old->man_mat_id;
            }

            $subcontractor_file = $mat_split_old->subcontractor_file;
            if ($subcontractor_file) {
                $file_copy = $subcontractor_file->replicate();
                $file_copy->commercial_offer_id = $com_offer_copy->id;
                $file_copy->save();

                $mat_split_copy->subcontractor_file_id = $file_copy->id;
            }
            $mat_split_copy->save();

            $replChildren = function ($old_parent, $new_parent) use (&$replChildren, $com_offer_copy, $complect_id_diff) {
                foreach ($old_parent->children as $child) {
                    if ($child->com_offer_id != $old_parent->com_offer_id) {continue;}
                    $child_copy = $child->replicate();
                    $child_copy->com_offer_id = $com_offer_copy->id;
                    $child_copy->parent_id = $new_parent->id;
                    $child_copy->man_mat_id = $complect_id_diff->where('old_id', $child->man_mat_id)['new_id'] ?? $child->man_mat_id;

                    $subcontractor_file = $child->subcontractor_file;
                    if ($subcontractor_file) {
                        $file_copy = $subcontractor_file->replicate();
                        $file_copy->commercial_offer_id = $com_offer_copy->id;
                        $file_copy->save();

                        $child_copy->subcontractor_file_id = $file_copy->id;
                    }
                    $child_copy->save();
                    $replChildren($child, $child_copy);
                }
            };
            $replChildren($mat_split_old, $mat_split_copy);
        }

        if ($com_offer_old->commercial_offer_works()->count()) {
            foreach ($com_offer_old->works as $work) {
                $new_work = $work->replicate();
                $new_work->commercial_offer_id = $com_offer_copy->id;
                $referenced_work = $manual_wv_id_map->where('manual_work_id', $new_work->manual_work_id)->first();
                $new_work->work_volume_work_id = $referenced_work ? $referenced_work->id : $work->work_volume_work_id;
                $new_work->save();
            }
        } else {
            foreach ($work_volume_old->raw_works as $work) {
                CommercialOfferWork::create([
                    "work_volume_work_id" => $work->id,
                    "commercial_offer_id" => $com_offer_copy->id,
                    "count" => $work->count,
                    "term" => $work->term,
                    "price_per_one" => $work->price_per_one,
                    "result_price" => $work->result_price,
                    "subcontractor_file_id" => $work->subcontractor_file_id,
                    "is_hidden" => $work->is_hidden,
                    "order" => $work->order,
                ]);
            }
        }

        $responsible_user = ProjectResponsibleUser::where('project_id', $project_id)->where('role', ($com_offer_copy->is_tongue ? 2 : 1))->first();

        if (!$responsible_user) {
            $responsible_user = ProjectResponsibleUser::create([
                'project_id' => $project_id,
                'user_id' => Auth::id(),
                'role' => $com_offer_copy->is_tongue ? 2 : 1,
            ]);
        }

        $work_task = Task::create([
            'project_id' => $project_id,
            'name' => 'Формирование КП' . ($com_offer_copy->is_tongue ? ' (шпунтовое направление)' : ' (свайное направление)'),
            'responsible_user_id' => $responsible_user->user_id,
            'contractor_id' => $target_project->contractor_id,
            'target_id' => $com_offer_copy->id,
            'expired_at' => Carbon::now()->addHours(24),
            'prev_task_id' => null,
            'status' => 5,
        ]);

        dispatchNotify(
            $work_task->responsible_user_id,
            'Новая задача «' . $work_task->name . '»',
            '',
            $com_offer_copy->is_tongue ?
                NotificationType::OFFER_CREATION_SHEET_PILING_TASK_NOTIFICATION :
                NotificationType::OFFER_CREATION_PILING_DIRECTION_TASK_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $work_task->task_route(),
                'task_id' => $work_task->id,
                'contractor_id' => $target_project->contractor_id,
                'project_id' => $target_project->id,
                'object_id' => $target_project->object_id,
            ]
        );

        $work_volume_copy->push();
        $com_offer_copy->push();

        DB::commit();

        return $com_offer_copy;
    }
//relations
    public function gantts()
    {
        return $this->hasMany(ComOfferGantt::class, 'com_offer_id', 'id');
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_commercial_offer_relations', 'commercial_offer_id' ,'contract_id');
    }

    /**
     * Relation to father project
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function is_uploaded()
    {
        if ($this->gantts->count() == 0 and $this->mat_splits()->count() == 0 and ($this->work_volume ? $this->work_volume->works()->count() == 0 : 1 )) {
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany | CommercialOfferMaterialSplit
     */
    public function mat_splits()
    {
        return $this->hasMany(CommercialOfferMaterialSplit::class, 'com_offer_id', 'id');
    }


    public function tasks()
    {
        return $this->all_tasks();
    }


    public function all_tasks()
    {
        return $this->hasMany( Task::class, 'target_id', 'id')->whereIn('status', Task::CO_STATUS);
    }


    public function unsolved_tasks()
    {
        return $this->hasMany( Task::class, 'target_id', 'id')
            ->whereIn('status', Task::CO_STATUS)
            ->where(function($q) {
                $q->orWhere('is_solved', 0)->orWhere('revive_at', '<>', null);
            });
    }


    public function get_requests()
    {
        return $this->hasMany(CommercialOfferRequest::class, 'commercial_offer_id', 'id');
    }


    public function works()
    {
        if ($this->commercial_offer_works()->count() > 0) {
            return $this->commercial_offer_works();
        } else {
            return $this->work_volume_works();
        }
    }


    public function worksForToggling()
    {
        if ($this->commercial_offer_works()->count() > 0) {
            return $this->commercial_offer_works();
        } else {
            return $this->work_volume_works_without_info();
        }
    }


    public function commercial_offer_works()
    {
        return $this->hasMany(CommercialOfferWork::class);
    }


    public function work_volume_works()
    {
        return $this->work_volume->works();
    }


    public function work_volume_works_without_info()
    {
        return $this->work_volume->worksWithoutManualInfo();
    }


    public function notes()
    {
        return $this->hasMany(CommercialOfferNote::class, 'commercial_offer_id', 'id')->where('note' , '!=', '');
    }


    public function requirements()
    {
        return $this->hasMany(CommercialOfferRequirement::class, 'commercial_offer_id', 'id')->where('requirement' , '!=', '');
    }


    public function work_volume()
    {
        return $this->belongsTo(WorkVolume::class, 'work_volume_id', 'id');
    }


    public function advancements()
    {
        return $this->hasMany(CommercialOfferAdvancement::class, 'commercial_offer_id', 'id')->where('description' , '!=', '');
    }


    public function signer()
    {
        return $this->hasOne(User::class, 'id', 'signer_user_id')
            ->leftJoin('groups', 'groups.id', '=', 'users.group_id')
            ->select('users.*', 'groups.name as group_name');
    }


    public function siblings()
    {
        return $this->hasMany(CommercialOffer::class, 'project_id', 'project_id')->where('is_tongue', $this->is_tongue);
    }
}

