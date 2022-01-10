<?php

namespace App\Models\MatAcc;

use App\Models\Contract\Contract;
use App\Models\Contractors\Contractor;
use App\Models\Group;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\Notification;
use App\Models\Task;
use App\Traits\NotificationGenerator;
use App\Traits\TimeCalculator;
use Illuminate\Database\Eloquent\Builder;

use Carbon\CarbonPeriod;
use App\Traits\Taskable;
use Illuminate\Database\Eloquent\Model;

use App\Models\ProjectObject;
use App\Models\User;
use App\Models\Manual\ManualMaterial;
use App\Models\Building\ObjectResponsibleUser;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Services\MaterialAccounting\MaterialAccountingService;


use \Carbon\Carbon;

class MaterialAccountingOperation extends Model
{
    use SoftDeletes, NotificationGenerator, TimeCalculator, Taskable;

    protected $fillable = [
        'type',
        'object_id_from',
        'object_id_to',

        'planned_date_from',
        'planned_date_to',
        'actual_date_from',
        'actual_date_to',

        'comment_from',
        'comment_to',
        'comment_author',

        'author_id',
        'sender_id',
        'recipient_id',
        'supplier_id',
        'responsible_RP',

        'status',
        'is_close',

        'reason',

        'parent_id',
        'contract_id',
    ];

    // for ttn
    public static $entities = [
        1 => 'ООО «СК ГОРОД»',
        2 => 'ООО «ГОРОД»',
        3 => 'ООО «СТРОЙМАСТЕР»',
        4 => 'ООО «РЕНТМАСТЕР»',
        5 => 'ООО «Вибродрилл Технология»',
        6 => 'ИП Исмагилов А.Д.',
        7 => 'ИП Исмагилов М.Д.',
    ];

    protected $appends = ['type_name', 'status_name', 'url', 'object_text',
        'object_to_text', 'object_from_text', 'address_text', 'created_date',
        'closed_date', 'edit_url', 'general_url', 'short_name'];

    public static $filter = [
        ['id' => 0, 'text' => 'Объект', 'db_name' => 'object_id_to'],
        ['id' => 1, 'text' => 'Материал', 'db_name' => 'manual_material_id'],
        ['id' => 2, 'text' => 'Автор', 'db_name' => 'author_id'],
        ['id' => 3, 'text' => 'Статус', 'db_name' => 'status'],
        ['id' => 4, 'text' => 'Тип', 'db_name' => 'type'],
        ['id' => 5, 'text' => 'Эталон', 'db_name' => 'reference'],
    ];

    public $units_name = [
        1 => 'т',
        2 => 'шт',
        3 => 'м.п',
        4 => 'м2',
        5 => 'м3',
    ];

    public $type_names = [
        1 => 'Поступление',
        2 => 'Списание',
        3 => 'Преобразование',
        4 => 'Перемещение',
    ];

    public $material_type_map = [
        1 => [2],
        2 => [2],
        3 => [4,5],
        4 => [4],
    ];

    public $eng_type_name = [
        1 => 'arrival',
        2 => 'write_off',
        3 => 'transformation',
        4 => 'moving',
    ];

    public $status_names = [
        1 => 'В работе',
        2 => 'Ожидает подтверждения',
        3 => 'Подтверждено',
        4 => 'Конфликт',
        5 => 'Черновик',
        6 => 'План',
        7 => 'Отмена',
        8 => 'Согласование',
    ];

    public $eng_status_names = [
        1 => 'work',
        2 => 'confirm',
        3 => 'complete',
        4 => 'conflict',
        5 => 'draft',
        6 => 'plan',
        7 => 'cancel',
        8 => 'agreement',
    ];

    const TASK_STATUSES = [21, 22, 23];

    /**
     * Scope for operations index page
     * @param Builder $query
     * @return Builder
     */
    public function scopeIndex(Builder $query)
    {
        $query->with(['object_from', 'object_to', 'author', 'sender', 'recipient', 'materials' => function($q) {
            $q->groupBy('manual_material_id', 'operation_id', 'used')->select('*')->with('manual');
        }])->where('type', '!=', 5)
            ->where('is_close', '!=', 1)
            ->orderBy('id', 'desc');

        return $query;
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getTypeNameAttribute()
    {
        return $this->type_names[$this->type];
    }

    public function getEnglishTypeNameAttribute()
    {
        return $this->eng_type_name[$this->type];
    }

    public function getStatusNameAttribute()
    {
        $today = Carbon::today()->format('d.m.Y');

        $text = '';

        if ($this->is_close) {
            if (!$this->contract_id and in_array($this->type, [1, 4]) and !in_array($this->object_id_to, [76, 192])) {
                $text = ' (Требуется прикрепить договор)';
            } else {
                $text = ' (Закрыта)';
            }
        }

        if ($this->materialsPartTo()->count() or $this->materialsPartFrom()->count()) {
            return $this->status_names[$this->status] . $text;
        }

        if (!($this->actual_date_from != null) and $this->status == 1 and $this->is_close != 1 and $this->type != 4) {
            return 'Запланировано';
        }


        if ($this->isMovingOperation() and $this->status == 1 and (!$this->actual_date_from and !$this->actual_date_to)) {
            return 'Запланировано';
        }

        return $this->status_names[$this->status] . $text;
    }

    public function getTotalWeigthAttribute()
    {
        $total_weigth = 0;
        foreach ($this->getParentMats()->whereIn('type', [3, 7]) as $material) {
            if ($material->unit == '1') {
                $total_weigth += $material->count;
            } else {
                if ($material->manual) {

                    $total_weigth += ($material->manual->convert_from($material->units_name[$material->unit])->where('unit', 'т')->first()->value ?? 0) * $material->count;
                } else {
                    $total_weigth += 0;
                }
            }
        }

        return round($total_weigth, 3);
    }

    public function getUrlAttribute()
    {
        if ($this->status == 7) {
            return route('building::mat_acc::closed_operation', $this->id);
        } elseif ($this->status == 8) {
            $this->status = 5;
        }

        return route('building::mat_acc::' . $this->eng_type_name[$this->type] . '::' . $this->eng_status_names[$this->status], $this->id);
    }

    public function getGeneralUrlAttribute()
    {
        return route('building::mat_acc::redirector', $this->id);
    }

    public function getEditUrlAttribute()
    {
        if ($this->status == 7) {
            return route('building::mat_acc::closed_operation', $this->id);
        } elseif ($this->status == 8) {
            return route('building::mat_acc::operations');
        }

        return route('building::mat_acc::' . $this->eng_type_name[$this->type] . '::' . 'edit', $this->id);
    }

    public function getClosedDateAttribute()
    {
        if ($this->is_close) {
            return Carbon::parse($this->updated_at)->format('d.m.Y H:i');
        }

        return '-';
    }

    public function getCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d.m.Y');
    }

    public function getObjectTextAttribute()
    {
        if ($this->object_id_from) {
            return $this->object_from->short_name ?? $this->object_from->name;
        } elseif($this->object_id_to) {
            return $this->object_to->short_name ?? $this->object_to->name;
        }
    }

    public function getObjectToTextAttribute()
    {
        if ($this->object_id_to) {
            return $this->object_to->short_name ?? $this->object_to->name;
        }
        return '';
    }

    public function getObjectFromTextAttribute()
    {
        if ($this->object_id_from) {
            return $this->object_from->short_name ?? $this->object_from->name;
        }
        return '';
    }

    public function getAddressTextAttribute()
    {
        if ($this->object_id_from) {
            return $this->object_from->address;
        } elseif($this->object_id_to) {
            return $this->object_to->address;
        }
    }


    public function getShortNameAttribute()
    {
        if ($this->object_id_from) {
            return $this->object_from->name_tag;
        } elseif($this->object_id_to) {
            return $this->object_to->name_tag;
        }
    }

    public function getLoweredTypeAttribute()
    {
        return mb_strtolower($this->type_name);
    }

    public function isWriteOffOperation(): bool
    {
        return $this->type == 2;
    }

    public function isMovingOperation(): bool
    {
        return $this->type == 4;
    }

    public function isWasDraft($oldStatus): bool
    {
        return $oldStatus == 5;
    }

    public function isConflict(): bool
    {
        return $this->status == 4;
    }

    public function object_from()
    {
        return $this->belongsTo(ProjectObject::class, 'object_id_from', 'id');
    }

    public function object_to()
    {
        return $this->belongsTo(ProjectObject::class, 'object_id_to', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Contractor::class, 'supplier_id', 'id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function responsible_user()
    {
        return $this->hasOne(MaterialAccountingOperationResponsibleUsers::class, 'operation_id', 'id');
    }

    public function responsible_users()
    {
        return $this->hasMany(MaterialAccountingOperationResponsibleUsers::class, 'operation_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id', 'id');
    }

    public function responsible_project_manager()
    {
        return $this->belongsTo(User::class, 'responsible_RP', 'id');
    }

    public function allMaterials()
    {
        return $this->hasMany(MaterialAccountingOperationMaterials::class, 'operation_id', 'id');
    }

    public function materials()
    {
        return $this->allMaterials()->whereNotIn('type', [8, 9, 10]);
    }

    public function materialsPart()
    {
        return $this->allMaterials()->whereIn('type', [8, 9])->with('comments');
    }

    public function materialsPartTo()
    {
        return $this->allMaterials()->where('type', 9);
    }

    public function materialsPartFrom()
    {
        return $this->allMaterials()->where('type', 8);
    }

    public function delete_tasks()
    {
        return $this->hasManyThrough(Task::class, MaterialAccountingOperationMaterials::class, 'operation_id' , 'target_id' , 'id', 'id')
            ->where('status', 22);
    }

    /**
     * Relation for all tasks that
     * can be created for operation
     * @return HasManyThrough
     */
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, MaterialAccountingOperationMaterials::class, 'operation_id' , 'target_id' , 'id', 'id')
            ->whereIn('status', self::TASK_STATUSES);
    }

    /**
     * Morph relation to tasks
     * @return MorphMany
     */
    public function tasksMorphed()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function contractTask()
    {
        return $this->hasOne(Task::class, 'target_id', 'id')
            ->where('status', 45);
    }

    /**
     * Relation only for unsolved tasks for operation
     * @return HasManyThrough
     */
    public function unsolved_tasks()
    {
        return $this->tasks()->where('is_solved', 0);
    }

    public function materialAddition()
    {
        return $this->hasOne(MaterialAccountingMaterialAddition::class, 'operation_id', 'id');
    }

    public function materialFiles()
    {
        return $this->hasMany(MaterialAccountingMaterialFile::class, 'operation_id', 'id');
    }

    public function images_author()
    {
        return $this->hasMany(MaterialAccountingOperationFile::class, 'operation_id', 'id')->where('type', 2)->where('author_type', 1);
    }

    public function all_images_author()
    {
        return $this->hasMany(MaterialAccountingOperationFile::class, 'operation_id', 'id')->where('type', 2);

    }

    public function images_sender()
    {
        return $this->hasMany(MaterialAccountingMaterialFile::class, 'operation_id', 'id')
            ->where('type', 2)
            ->whereHas('operationMaterial', function ($q) {
                $q->where('type', 8);
            });
    }

    public function documents_sender()
    {
        return $this->hasMany(MaterialAccountingMaterialFile::class, 'operation_id', 'id')
            ->where('type', 1)
            ->whereHas('operationMaterial', function ($q) {
                $q->where('type', 8);
            });
    }

    public function images_recipient()
    {
        return $this->hasMany(MaterialAccountingMaterialFile::class, 'operation_id', 'id')
            ->where('type', 2)
            ->whereHas('operationMaterial', function ($q) {
                $q->where('type', 9);
            });
    }

    public function documents_author()
    {
        return $this->hasMany(MaterialAccountingOperationFile::class, 'operation_id', 'id')->where('type', 1)->where('author_type', 1);
    }

    public function all_documents_author()
    {
        return $this->hasMany(MaterialAccountingOperationFile::class, 'operation_id', 'id')->where('type', 1);

    }

    public function documents_recipient()
    {
        return $this->hasMany(MaterialAccountingMaterialFile::class, 'operation_id', 'id')
            ->where('type', 1)
            ->whereHas('operationMaterial', function ($q) {
                $q->where('type', 9);
            });
    }

    public function child()
    {
        return $this->hasOne(MaterialAccountingOperation::class, 'parent_id', 'id');
    }

    public function grandChild()
    {
        return $this->child()->with('grandChild')->select('id', 'actual_date_to', 'actual_date_from', 'planned_date_to', 'planned_date_from', 'parent_id');
    }

    public function getParentMats()
    {
        $mats = $this->materials;
        $mats->each->setAppends(['material_name']);
        $parent_mats = collect([]);

        if ($this->parent()->count() > 0) {
            $parent_mats = $this->parent->getParentMats();

            foreach ($parent_mats as $parent_mat) {
                $existing_mat = $mats->where('manual_material_id', $parent_mat->manual_material_id)->where('type', $parent_mat->type)->first();
                if ($existing_mat) {
                    $existing_mat->count += $parent_mat->count;
                } else {
                    $mats->push($parent_mat);
                }
            }
        }

        return $mats;
    }

    public function getParentMatParts()
    {
        $mats = $this->materialsPart;

        if ($this->parent()->count() > 0) {
            $mats = $mats->merge($this->parent->getParentMatParts());
        }


        return $mats;
    }

    public function parent()
    {
        return $this->belongsTo(MaterialAccountingOperation::class, 'parent_id', 'id');
    }

    public function grandParent()
    {
        return $this->parent()->with('grandParent')->with('parentMats')->select('id', 'parent_id');
    }

    public function contract()
    {
        return $this->belongsTo( Contract::class, 'contract_id', 'id');
    }

    public function getGratestParent()
    {
        if($this->parent){
            return $this->parent->getGratestParent();
        }

        return $this;
    }

    public function hasHistory()
    {
        return $this->materialsPartTo()->count() || $this->materialsPartFrom()->count() || $this->parent()->count();
    }

    public function hasAccessFrom()
    {
        return MaterialAccountingOperationResponsibleUsers::where('operation_id', $this->id)->whereIn('type', [0, 1])->where('user_id', Auth::id())->count() >= 1;
    }

    public function hasAccessTo()
    {
        return MaterialAccountingOperationResponsibleUsers::where('operation_id', $this->id)->whereIn('type', [0, 2])->where('user_id', Auth::id())->count() >= 1;
    }

    public function hasAccess()
    {
        return MaterialAccountingOperationResponsibleUsers::where('operation_id', $this->id)->where('type', 0)->where('user_id', Auth::id())->count() >= 1;
    }

    public function isAuthor()
    {
        return $this->author_id == Auth::id();
    }

    public function checkClosed()
    {
        return !($this->is_closed or $this->status == 7)?: abort(403);
    }


    public function materialDifference($main_materials, $part_materials)
    {
        // if ($is_dd) dump($main_materials, $part_materials);
        if (!$part_materials) {
            return [$main_materials];
        }

        $new_materials = collect();
        $link = 0;
        foreach ($part_materials->whereIn('base_id', $main_materials->pluck('base_id')) as $part_material) {
            $main_material = $main_materials->where('base_id', $part_material->base_id)->whereNotIn('id', $new_materials->pluck('id'))->first();

            if (!$main_material) {
                $main_material = $new_materials->where('base_id', $part_material->base_id)->first();
                $link = 1;
            }

            if ($main_material) {
                if ($part_material->count >= $main_material->count) {
                    $main_material->count = 0;
                } else {
                    $main_material->count -= $part_material->count;
                }
                if (!$link) {
                    $new_materials->push($main_material);
                }
            }
            $link = 0;
        }
        // if ($is_dd) dd($main_materials);
        return $main_materials->where('count', '>', 0)->values();
    }

    public function checkProblem($operation, $materials)
    {
        $units_name = MaterialAccountingOperationMaterials::getModel()->units_name;
        foreach ($materials as $material) {
            $mat = ManualMaterial::where('manual_materials.id', $material['material_id'])
                ->withTrashed()
                ->with('parameters')
                ->leftJoin('manual_material_categories', 'manual_material_categories.id', 'manual_materials.category_id')
                ->select('manual_material_categories.id as cat_id', 'manual_material_categories.category_unit', 'manual_materials.*')
                ->first();

            $existPart = $operation->materialsPart()
                ->with('manual')
                ->where('manual_material_id', $mat->id)
                ->where('type', 8)
                ->select('*', DB::raw('sum(count) as count'))
                ->groupBy(['type', 'manual_material_id'])
                ->first();

            $period = CarbonPeriod::create($operation->planned_date_from, Carbon::today());
            $count = round($material['material_count'], 3);

            foreach ($period as $date) {
                $comments = isset($material['comments']) ? $material['comments'] : [];
                if ($material['base_id'] == true and $material['base_id'] != 'undefined') {
                    $base_comments = MaterialAccountingBase::where('id', $material['base_id'])
                        ->with('historyBases')
                        ->where('date', $date->format('d.m.Y'))
                        ->first();
                    if ($base_comments and count($comments) == 0) {
                        $comments = $base_comments->comments()->get();
                    }
                }
                $base = MaterialAccountingBase::query()->where([
                    'object_id' => $operation->object_id_from,
                    'manual_material_id' => $material['material_id'],
                    'date' => $date->format('d.m.Y'),
                    'used' => $material['used'] ?? 0,
                ]);

                if ($comments and count($comments) > 0) {
                    //we are looking for the same comments.
                    foreach ($comments as $comment) {
                        $base->whereHas('comments', function ($comm_q) use ($comment) {
                            $comm_q->where('comment', $comment['comment']);
                        });
                    }
                    $base->has('comments', count($comments));
                } else {
                    // or absence of comments
                    $base->whereDoesntHave('comments');
                }
                $base = $base->first();

                if (!isset($base->unit)) {
                    return true;
                }

                $unit = null;
                if (isset((new MaterialAccountingOperationMaterials)->units_name[$material['material_unit']])) {
                    $unit = (new MaterialAccountingOperationMaterials)->units_name[$material['material_unit']];
                }
                $unit = $unit ?? $material['material_unit'];
                if ($base->unit == $unit) {
                } else {
                    $convertParam = $mat
                            ->convert_from($unit)
                            ->where('unit', $base->unit)->first()->value ?? 0;
                    if ($convertParam) {
                        $count = $count * $convertParam;
                    } else {
//                        $message = 'Невозможно списать  ' . $mat->name . ' т.к. нет параметра для перевода в единицу измерения (' . $base->unit . '), в которой он лежит на объекте';

                        return true;
                    }
                }

                if ($existPart) {
                    if ($base->unit == $existPart->units_name[$existPart->unit]) {
                    } else {
                        $convertParam = $mat
                                ->convert_from($unit)
                                ->where('unit', $base->unit)->first()->value ?? 0;

                        if ($convertParam) {
                            $existPart->count = $existPart->count * $convertParam;
                        } else {
                            return true;
                        }
                    }
                }
                if (!isset($base->count) or (round($count, 3)) > round($base->count + ($existPart->count ?? 0), 3) or !$base->count) {
                    return true;
                }

            }
        }

        return false;
    }

    public function checkControlTask($status_result = 'accept')
    {
        // find task and solve it
        $controlTask = Task::where('is_solved', 0)->where('status', 21)->where('target_id', $this->id)->first();
        if ($controlTask) {
            $this->load('author');

            // update task
            $controlTask->result = $status_result === 'accept' ? 1 : 2;
            $controlTask->final_note = $controlTask->descriptions[$controlTask->status] . $controlTask->results[$controlTask->status][$controlTask->result];
            $controlTask->save();

            if ($status_result == 'decline') {
                //notify operation creator
                $notify = Notification::create([
                    'name' => 'Ваша операция списания была отклонена',
                    'task_id' => $controlTask->id,
                    'user_id' => $this->author->id,
                    'type' => 13
                ]);
            }

            $controlTask->solve_n_notify();
        }
        // otherwise, do nothing
    }


    public function generateOperationDeclineNotifications($oldStatus = null)
    {
        if ($this->isWasDraft($oldStatus))
            return $this->generateOperationDraftDeclineNotification();

        return $this->generateStandardOperationDeclineNotification();
    }

    public function generateOperationDraftDeclineNotification()
    {
        $notification = new Notification();
        $notification->save();
        $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
        $notification->update([
            'name' => $this->generateOperationDraftDeclinedNotificationText(),
            'user_id' => $this->author->id,
            'target_id' => $this->id,
            'status' => 7,
            'type' => 58
        ]);
    }

    public function generateOperationDraftDeclinedNotificationText()
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $userFullName = auth()->user()->long_full_name;
        $text = "Пользователь {$userFullName} отклонил запрос на {$typeLowered} материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" . (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '');

        if ($this->isMovingOperation())
            $text = "Пользователь {$userFullName} отклонил запрос на {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) . " на объект " . ($this->object_to->name_tag) . ";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to}";

        return $text;
    }

    public function generateStandardOperationDeclineNotification()
    {
        $user_ids = [$this->author->id, $this->responsible_user->user_id];
        $user_ids = $this->updateUserIdsArray($user_ids);

        foreach (array_unique($user_ids) as $user) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationDeclinedNotificationText(),
                'user_id' => $user,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 55
            ]);
        }
    }

    public function updateUserIdsArray($user_ids)
    {
        if ($this->isMovingOperation()) {
            array_pop($user_ids);
            array_push($user_ids, ...$this->responsible_users()->pluck('user_id')->toArray());
        } elseif ($this->isWriteOffOperation()) {
            array_push($user_ids, Group::find(6)->getUsers()->first()->id);
        }

        return $user_ids;
    }

    public function generateOperationDeclinedNotificationText()
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $text = "Операция {$typeLowered} материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" .
            (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '') .
            " отменена";

        if ($this->isMovingOperation())
            $text = "Операция {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) ." на объект " . ($this->object_to->name_tag) . ";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to} отменена";

        return $text;
    }

    public function generateDraftAcceptNotification($oldAuthor)
    {
        $notification = new Notification();
        $notification->save();
        $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
        $notification->update([
            'name' => $this->generateOperationDraftAcceptNotificationText(),
            'user_id' => $oldAuthor,
            'target_id' => $this->id,
            'status' => 7,
            'type' => 57
        ]);
    }

    public function generateOperationDraftAcceptNotificationText()
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $userFullName = $this->author->long_full_name;
        $text = "Пользователь {$userFullName} согласовал запрос на {$typeLowered} материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" . (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '');

        if ($this->isMovingOperation())
            $text = "Пользователь {$userFullName} согласовал запрос на {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) ." на объект " . ($this->object_to->name_tag) . ";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to}";

        return $text;
    }

    public function generatePartSendNotification($partSendType = false)
    {
        if ($this->isMovingOperation())
            return $this->generateMovingPartSendNotifications($partSendType);

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
        $notification->update([
            'name' => $this->generateOperationPartSaveNotificationText($partSendType),
            'user_id' => $this->author_id,
            'target_id' => $this->id,
            'status' => 7,
            'type' => 59
        ]);

        if ($this->isWriteOffOperation()) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationPartSaveNotificationText($partSendType),
                'user_id' => Group::find(6)->getUsers()->first()->id,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 59
            ]);
        }
    }

    public function generateMovingPartSendNotifications($partSendType = false)
    {
        $users = [$this->author_id, $this->responsible_users()->where('type', $partSendType == 8 ? 1 : 2)->first()->user_id ?? 1];

        foreach ($users as $user) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationPartSaveNotificationText($partSendType),
                'user_id' => $user,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 59
            ]);
        }
    }

    public function generateOperationPartSaveNotificationText($partSendType = false)
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $userFullName = auth()->user()->long_full_name;
        $text = "Выполнено частичное {$typeLowered} материала пользователем {$userFullName} по операции «{$this->type_name}» материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" . (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '');

        if ($this->isMovingOperation())
            $text = ($partSendType == 9 ? "Выполнено частичное поступление материала на объект " . ($this->object_to->name_tag) . "" : "Выполнена частичная отправка материала с объекта " . ($this->object_from->name_tag) ."") . " пользователем {$userFullName} по операции «{$this->type_name}» материалов c объекта " . ( $this->object_from->name_tag) . " на объект " . ($this->object_to->name_tag) . ";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to}";

        return $text;
    }

    public function generateOperationEndNotifications($sendType = false)
    {
        if ($this->isMovingOperation())
            return $this->generateMovingEndNotifications($sendType);

        $users = $this->type == 2 ? [$this->author->id, Group::find(6)->getUsers()->first()->id] : [$this->author->id];

        foreach ($users as $userId) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationEndNotificationText($sendType),
                'user_id' => $userId,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 60
            ]);
        }
    }

    public function generateMovingEndNotifications($sendType)
    {
        $users = [$this->author->id, $this->responsible_users()->where('type', $sendType == 1 ? 1 : 2)->first()->user_id ?? 1];

        foreach ($users as $userId) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationEndNotificationText($sendType),
                'user_id' => $userId,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 60
            ]);
        }
    }

    public function generateOperationEndNotificationText($sendType = false)
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $userFullName = auth()->user()->long_full_name;
        $text = "Пользователь {$userFullName} завершил операцию {$typeLowered} материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" . (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '') .
            '. Требуется подтвердить операцию';

        if ($this->isMovingOperation())
            $text = ($sendType == 2 ? "Пользователь {$userFullName} завершил получение материалов на объект " . ($this->object_to->name_tag) . "" : "Пользователь {$userFullName} завершил отправку материалов с объекта " . ($this->object_from->name_tag) . "") . " по операции {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) . " на объект " . ($this->object_to->name_tag) . ";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to}";

        return $text;
    }

    public function generateOperationAcceptNotifications()
    {
        if ($this->isMovingOperation())
            return $this->generateMovingAcceptNotifications();

        $users = $this->type == 2 ? [$this->responsible_user->user_id, Group::find(6)->getUsers()->first()->id] : [$this->responsible_user->user_id];

        foreach ($users as $userId) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationAcceptNotificationText(),
                'user_id' => $userId,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 61
            ]);
        }
    }

    public function generateMovingAcceptNotifications()
    {
        $users = $this->responsible_users()->whereIn('type', [1, 2])->pluck('user_id')->toArray();

        foreach ($users as $userId) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationAcceptNotificationText(),
                'user_id' => $userId,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 61
            ]);
        }
    }

    public function generateOperationAcceptNotificationText()
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $userFullName = auth()->user()->long_full_name;
        $text = "Пользователь {$userFullName} подтвердил операцию {$typeLowered} материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" . (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '') .
            '. Операция закрыта';

        if ($this->isMovingOperation())
            $text = "Пользователь {$userFullName} подтвердил операцию {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) . " на объект " . ($this->object_to->name_tag) . ";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to}" .
                '. Операция закрыта';

        return $text;
    }

    public function generateOperationConflictNotifications()
    {
        if ($this->status != 4) return;
        $user_ids = [$this->author->id, $this->responsible_user->user_id];
        $user_ids = $this->updateUserIdsArray($user_ids);

        foreach ($user_ids as $user) {
            $notification = new Notification();
            $notification->save();
            $notification->additional_info = '. Для подробностей перейдите по ссылке: ' . PHP_EOL . $this->general_url;
            $notification->update([
                'name' => $this->generateOperationConflictNotificationText(),
                'user_id' => $user,
                'target_id' => $this->id,
                'status' => 7,
                'type' => 62
            ]);
        }
    }

    public function generateOperationConflictNotificationText()
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $text = "Операция на {$typeLowered} материалов на объекте: {$this->object_text};" .
            " в периоде выполнения: {$this->planned_date_from}" . (! in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '') .
            ', не может быть выполнена';

        if ($this->isMovingOperation())
            $text = "Операция {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) ." на объект " . ( $this->object_to->name_tag) .";" .
                " в периоде выполнения: {$this->planned_date_from} - {$this->planned_date_to}" .
                ', не может быть выполнена';

        return $text;
    }

    public function generateDraftUpdateNotifications()
    {
        if ($this->status != 5) return;

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = '. Перейти к операции можно по ссылке: ' . PHP_EOL . $this->general_url;
        $notification->update([
            'name' => $this->generateOperationDraftUpdateNotificationText(),
            'user_id' => $this->responsible_RP,
            'target_id' => $this->id,
            'status' => 7,
            'type' => 64
        ]);

    }

    public function generateOperationDraftUpdateNotificationText()
    {
        $typeLowered = $this->getLoweredTypeAttribute();
        $text = "Пользователь {$this->author->long_full_name} обновил запрос на {$typeLowered} материалов на объекте: {$this->object_text};" .
            " Период выполнения: {$this->planned_date_from}" . (!in_array($this->type, [2, 3]) ? " - {$this->planned_date_to}" : '');

        if ($this->isMovingOperation($this))
            $text = "Пользователь {$this->author->long_full_name} обновил запрос на {$typeLowered} материалов c объекта " . ($this->object_from->name_tag) . " на объект " . ($this->object_to->name_tag) . ";" .
                " Период выполнения: {$this->planned_date_from} - {$this->planned_date_to}";

        return $text;
    }

    /**
     * This function return true if we have
     * draft that updated nice, without conflicts
     * @param $oldStatus
     * @param $is_conflict
     * @return bool
     */
    public function wasDraftAndUserCanWorkOnlyWithDraftsAndNoConflictInOperation($oldStatus, $is_conflict): bool
    {
        return $this->isWasDraft($oldStatus) and auth()->user()->isOperationDrafter($this->getEnglishTypeNameAttribute()) and !$is_conflict;
    }

    /**
     * This function return true if we have
     * draft that updated by user who can
     * create operations of given $type
     * @param $oldStatus
     * @param $is_conflict
     * @return bool
     */
    public function wasDraftAndUserCanCreateOperationAndNoConflictInOperation($oldStatus, $is_conflict)
    {
        return $this->isWasDraft($oldStatus) and auth()->user()->isOperationCreator($this->getEnglishTypeNameAttribute()) and !$is_conflict;
    }

    public function send(Request $request)
    {
        $result = (new MaterialAccountingService($this))->send($request);
        $this->update_fact();

        if (($result['status'] ?? null) == 'success') {
            return true;
        } else {
            return ['message' => ($result['message'] ?? 'Ошибка')];
        }
    }

    public function partSend(Request $request)
    {
        $result = (new MaterialAccountingService($this))->partSend($request);

        return $result;
    }

    public function compareMaterials()
    {
        return (new MaterialAccountingService($this))->compareMaterials();
    }


    /**
     * Function create certificate control task for operation
     * @return void
     */
    public function makeCertificateControlTask(): void
    {
        $ilchuk = User::find(User::HARDCODED_PERSONS['certificateWorker']);
        $newTask = $this->tasksMorphed()->create([
            'name' => 'Контроль наличия сертификатов в операции',
            'responsible_user_id' => $ilchuk->id,
            'status' => 43,
            'expired_at' => $this->addHours(8),
        ]);
        $this->generateCertificateControlTaskNotification($newTask);
    }


    public function update_fact()
    {
        $result_mats = $this->materials()
            ->whereIn('type', (in_array($this->type, [3, 4]) ? [1, 2] : [1]))
            ->delete();

        if (in_array($this->type, [3, 4])) {
            MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this, $this->materialsPartTo->toArray(), 2);
            MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this, $this->materialsPartFrom->toArray(), 1);
        } elseif(in_array($this->type, [1, 2])) {
            MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this, $this->materialsPartTo->toArray(), 1);
            MaterialAccountingOperationMaterials::getModel()->createOperationMaterials($this, $this->materialsPartFrom->toArray(), 1);

        } else {
            abort(405, 'Неверные данные');
        }
    }
}
