<?php

namespace App\Models;

use App\Models\Contractors\Contractor;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Traits\Notificationable;
use App\Traits\NotificationGenerator;
use App\Traits\SmartSearchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\MatAcc\MaterialAccountingOperationMaterials;

class Task extends Model
{
    use SoftDeletes, Notificationable, NotificationGenerator, SmartSearchable;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'contractor_id',
        'user_id',
        'responsible_user_id',
        'contact_id',
        'incoming_phone',
        'internal_phone',
        'expired_at',
        'final_note',
        'is_solved',
        'status',
        'is_seen',
        'status_result',
        'notify_send',
        'target_id',
        'revive_at',
        'result',
        'prev_task_id',
    ];

    protected $appends = ['target', 'created_at_formatted'];

    public $notify_codes = [
        0 => 'Новая/закрытая задача',
        1 => 'Уведомление о 30%',
        2 => 'Просрочена'
    ];

    public static $task_status = [
        1 => 'Стандартная задача',
        2 => 'Обработка входящего вызова',
        3 => 'Расчёт объёмов (шпунтовое направление)',      //work_volume
        4 => 'Расчёт объёмов (свайное направление)',        //work_volume
        5 => 'Формирование КП',                             //commercial_offer
        6 => 'Согласование КП с заказчиком',                //commercial_offer
        7 => 'Формирование договора',                       //contract (договор)
        8 => 'Согласование договора',                       //contract (договор)
        9 => 'Контроль подписания договора',                //contract (договор)
        10 => 'Контроль подписания договора (повторно)',    //contract (договор)
        11 => 'Контроль согласования договора',             //contract (договор)
        12 => 'Контроль изменений КП',                      //project (null) commercial_offer
        13 => 'Отправка договора на согласование',          //contract (договор) !!! UPD REMOVED !!!
        14 => 'Назначение ответственного за ОР (шпунт)',    //user (null)
        15 => 'Назначение ответственного за КП (шпунт)',    //user (null) commercial_offer
        16 => 'Согласование КП',                            //commercial_offer
        17 => 'Обработка заявки на ОР',                     //work_volume_request
        18 => 'Контроль выполнения ОР',                     //work_volume
        19 => 'Контроль удаления контрагента',              //contractor_remove
        20 => 'Контроль удаления договора',                 //contract_remove
        21 => 'Контроль списания',                          //mat acc write off operation
        22 => 'Контроль удаления материала',                          //mat acc write off operation
        23 => 'Контроль редактирования материала',                          //mat acc write off operation
        24 => 'Назначение ответственного руководителя проектов (сваи)',
        25 => 'Назначение ответственного руководителя проектов (шпунт)',
        26 => 'Назначение исполнителя заявки на неисправность',
        27 => 'Согласование продления использования техники',
        28 => 'Согласование заявки на технику',
        29 => 'Подтверждение начала использования техники',
        30 => 'Обработка заявки на перемещение техники',
        31 => 'Подтверждение отправки техники',
        32 => 'Подтверждение получения техники',
        33 => 'Контроль заявки на неисправность',
        35 => 'Контроль выполнения заявки на неисправность',
        36 => 'Отметка времени использования техники',
        37 => 'Проверка изменений в информации о контрагенте',
        38 => 'Согласование даты операции',
        39 => 'Назначение ответственного за учёт времени в проекте',    // human resources
        40 => 'Контроль явки',    // human resources
        41 => 'Контроль рабочего времени',    // human resources
        43 => 'Контроль наличия сертификатов',
        45 => 'Контроль договора в операциях',
    ]; //TODO: adding something here? check trello first


    public $descriptions = [
        1 => 'Задача выполнена',
        2 => 'Задача выполнена',
        3 => 'Произведён расчёт объемов работ', //work_volume
        4 => 'Произведён расчёт объемов работ', //work_volume
        5 => 'Коммерческое предложение сформировано', //commercial_offer
        6 => 'Коммерческое предложение ', // .$results //commercial_offer
        7 => 'Сформирован новый договор', //contract (договор)
        8 => 'Договор ', // .$results //contract (договор)
        9 => 'Договор ', // .$results //contract (договор)
        10 => 'Договор ', // .$results //contract (договор)
        11 => 'Договор ', // .$results //contract (договор)
        12 => 'Задача выполнена', //project (null) commercial_offer
        14 => 'Исполнителем на расчёт объёмов назначен сотрудник ', // . ФИО //user (null)
        15 => 'Исполнителем на формирование коммерческого предложения назначен сотрудник ', // . ФИО //user (null) commercial_offer
        16 => 'Коммерческое предложение ', // .$results //commercial_offer
        17 => 'Пользователь ', // .$results //work_volume_request
        18 => 'Контролируемый объём работ был ', // .$results //work_volume
        19 => 'Запрашиваемый контрагент ', // .$results //contractor_remove
        20 => 'Контролируемый договор ', // .$results //contract_remove
        21 => 'Контролируемое списание ', // .$results //mat acc write off operation
        22 => 'Запрос на удаление ', //mat acc request delete
        23 => 'Запрос на редактирование ', //mat acc request update
        27 => 'Запрос продления использования техники',
        28 => 'Согласование заявки на технику',
        29 => 'Подтверждение факта начала использования техники ',
        30 => 'Заявка на перемещение ',
        31 => 'зафиксирована отправка',
        32 => 'зафиксировано получение',
        38 => 'Операция была ',
        37 => 'Данные',
        45 => 'Договор был прикреплен',
    ];

    public $results = [
        // key = $task->status, value = array[$task->result => text]
        6 => [1 => 'согласовано', 2 => 'перенесено в архив', 3 => 'перенесено на согласование ', 4 => 'отклонено. Требуются изменения'],
        8 => [1 => 'согласован', 2 => 'не согласован'], //contract (договор)
        9 => [1 => 'подписан заказчиком', 2 => 'не подписан. Заказчиком предоставлено гарантийное письмо', 3 => 'не подписан заказчиком'], //contract (договор)
        10 => [1 => 'подписан заказчиком', 2 => 'не подписан заказчиком'], //contract (договор)
        11 => [1 => 'согласован с заказчиком', 3 => 'не согласован с заказчиком'], //contract (договор)
        16 => [1 => 'согласовано', 2 => 'не согласовано'], //commercial_offer
        17 => [1 => ' отправил новую заявку', 2 => ' отказался от заявки'], //work_volume_request
        18 => [1 => 'согласован', 2 => 'отклонён'], //work_volume
        19 => [1 => 'был удалён', 2 => 'не был удалён'], //contractor_remove
        20 => [1 => 'был удалён', 2 => 'не был удалён'], //contract_remove
        21 => [1 => 'согласовано', 2 => 'не согласовано'], //mat acc write off operation
        22 => [1 => 'одобрен', 2 => 'не одобрен'], //mat acc delete part material
        23 => [1 => 'одобрен', 2 => 'не одобрен'], //mat acc update part material
        27 => [1 => 'одобрен.', 2 => 'отклонен.'],
        28 => [1 => 'согласована', 2 => 'отклонена'],
        29 => [1 => 'использование начато'],
        30 => [1 => 'обработана', 2 => 'отклонена', 3 => 'удержана'],
        38 => [1 => 'согласована', 2 => 'отклонена'], //mat acc  operations
        37 => [1 => 'были изменены.', 2 => 'не были изменены.'],
        36 => [1 => 'проставлена в полном объёме', 2 => 'не проставлена'],
    ];

    const POSTPONED_STATUS = [6, 8, 9, 10, 13, 11];

    const WV_STATUS = [3, 4, 17, 18];

    const CO_STATUS = [5, 6, 15, 16];

    const PROJ_STATUS = [12, 14];

    const CONTR_STATUS = [7, 8, 9, 10, 11, 13, 20];

    const CONTRACTOR_STATUS = [19];

    const MATACC_STATUS = [21, 22, 23];

    const TECHACC_STATUS = [28, 29, 30];

    const LINK_TO_TICKET_STATUS = [28, 29, 30, 31, 32, 34];

    const LOWER_WORK_HOUR_LIMIT = 8;

    const UPPER_WORK_HOUR_LIMIT = 19;

    const ADDITIONAL_DATE_FORMAT = 'd.m.Y H:i';

    public function getGetResultAttribute()
    {
        $result = $this->result ?: $this->is_solved;
        $text = $this->descriptions[$this->status] . ' ' . ($this->results[$this->status][$result] ?? '');
        return $text;
    }

    /**
     * Getter for created_at formatting
     * @return string
     */
    public function getCreatedAtFormattedAttribute($date)
    {
        return Carbon::parse($date)->format(self::ADDITIONAL_DATE_FORMAT);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getExpiredAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function task_route()
    {
        if ($this->status == 1) return route('tasks::card', $this->id);
        elseif ($this->status == 2) return route('tasks::new_call', $this->id);
        elseif ($this->status == 19) return route('contractors::remove_task', $this->id);
        elseif ($this->status == 21) return route('building::mat_acc::write_off::control', $this->id);
        elseif ($this->status == 38) return route('building::mat_acc::write_off::control', $this->id);
        elseif ($this->status == 22) return route('building::mat_acc::delete_part_task', $this->id);
        elseif ($this->status == 23) return route('building::mat_acc::update_part_task', $this->id);
        elseif ($this->status == 43) return route('building::mat_acc::certificateless_task', $this->id);
        elseif ($this->status == 37) return route('tasks::slim_task', $this->id);
        elseif ($this->status == 45) return route('tasks::slim_task', $this->id);
        elseif (in_array($this->status, self::LINK_TO_TICKET_STATUS)) return route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $this->taskable->id ?? '']);
        elseif ($this->status >= 26 and $this->status < 36) return route('tasks::tech_task', $this->id);
        elseif ($this->status == 36) return route('tasks::partial_36', $this->id);
        elseif ($this->status === 40) return route('human_resources.timecard_day.appearance_task', $this->id);
        // TODO ADD SOMETHING HERE
        elseif ($this->status === 41) return route('human_resources.timecard_day.working_time_task', $this->id);
        else return route('tasks::common_task', $this->id);
    }

    public function getTargetAttribute()
    {
        if ($this->status == 23) return MaterialAccountingOperationMaterials::with('manual', 'updated_material.manual')->find($this->target_id);
        else return false;
    }

    public function chief()
    {
        if (in_array($this->status, [14, 15])) return User::where('group_id', 5/*3*/)->first()->id; // Генеральный директор
        elseif (in_array($this->status, [2, 4, 5, 6, 12, 16])) return User::where('group_id', 50/*7*/)->first()->id; // Директор по развитию
        elseif (in_array($this->status, [1])) return $this->user_id; // Создатель задачи
        elseif (in_array($this->status, [3, 7, 8, 9, 10, 11, 13])) return User::where('group_id', 53/*16*/)->first()->id; // Начальник ПТО
        else {
            return User::where('group_id', 5/*3*/)->first()->id;
        }
    }

    public function taskable()
    {
        return $this->morphTo();
    }


    public function responsible_user()
    {
        return $this->belongsTo(User::class, 'responsible_user_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function redirects()
    {
        return $this->hasMany(TaskRedirect::class, 'task_id', 'id');
    }


    public function task_files()
    {
        return $this->hasMany(TaskFile::class, 'task_id', 'id');
    }


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }

    public function solve()
    {
        if ($this->revive_at) {
            $this->revive_at = null;
        } elseif ($this->is_solved == 0) {
            $this->is_solved = 1;
        } else {
            return false;
        }

        if (!$this->result) {
            $this->result = 1;
        }
        $this->save();

        return true;
    }


    public function solve_n_notify()
    {
        $was_solved = $this->solve();

        if ($was_solved) {
            Notification::create(['name' => 'Задача «' . $this->name . '» закрыта',
                'task_id' => $this->id,
                'user_id' => $this->responsible_user_id,
                'contractor_id' => $this->project_id ? Project::find($this->project_id)->contractor_id : null,
                'project_id' => $this->project_id ? $this->project_id : null,
                'object_id' => $this->project_id ? Project::find($this->project_id)->object_id : null,
                'type' => 3
            ]);
        }
    }


    public function is_unsolved(...$statuses)
    {
        if (!$statuses) {
            $statuses = array_keys($this::$task_status);
        }

        if ($this->is_solved == 1 and $this->revive_at == null) {
            return false;
        } else {
            return in_array($this->status, array_merge($statuses));
        }
    }


    public function is_overdue()
    {
        $expired_at = Carbon::create($this->expired_at);
        $created_at = $this->created_at;

        $term = $expired_at->diffInHours($created_at);
        $time_passed_for_overdue = $term * 0.60;

        return Carbon::parse($created_at)->addHours($time_passed_for_overdue) < Carbon::now();
    }

    public static function moveTasks($tasks, $old_user_id, $new_user_id, $vacation_id, $reason = 'Отпуск пользователя')
    {
        DB::beginTransaction();

        $insert = [];
        if ($reason == 'Отпуск пользователя') {
            foreach ($tasks as $task) {
                $insert[] = [
                    'vacation_id' => $vacation_id,
                    'task_id' => $task->id,
                    'old_user_id' => $old_user_id,
                    'responsible_user_id' => $new_user_id,
                    'redirect_note' => $reason,
                    'created_at' => Carbon::now()
                ];
                $task->update(['responsible_user_id' => $new_user_id]);
            }
        } else {
            foreach ($tasks as $task) {
                $insert[] = [
                    'task_id' => $task->id,
                    'old_user_id' => $old_user_id,
                    'responsible_user_id' => $new_user_id,
                    'redirect_note' => $reason,
                    'created_at' => Carbon::now()
                ];
                $task->update(['responsible_user_id' => $new_user_id]);
            }
        }

        TaskRedirect::insert($insert);

        DB::commit();

        return 'Tasks Movin\'';
    }


    public static function moveTasksBack($tasks, $old_user_id, $new_user_id, $vacation_id, $reason = 'Выход из отпуска')
    {
        DB::beginTransaction();

        $insert = [];
        foreach ($tasks as $task) {
            $insert[] = [
                'vacation_id' => $vacation_id,
                'task_id' => $task->id,
                'old_user_id' => $old_user_id,
                'responsible_user_id' => $new_user_id,
                'redirect_note' => $reason
            ];
            $task->update(['responsible_user_id' => $new_user_id]);
        }

        TaskRedirect::insert($insert);

        DB::commit();

        return 'Tasks Movin\' Back';
    }

    public function create_notify($name, $type)
    {
        Notification::create([
            'name' => $name,
            'task_id' => $this->id,
            'user_id' => $this->responsible_user_id,
            'contractor_id' => $this->contractor_id ?? null,
            'project_id' => $this->project_id ?? null,
            'object_id' => isset($this->project->object->id) ? $this->project->object->id : null,
            'type' => $type
        ]);
    }


    public function prev_task()
    {
        return $this->hasOne(Task::class, 'id', 'prev_task_id')
            ->with('responsible_user', 'author', 'redirects', 'task_files')
            ->leftJoin('users', 'users.id', '=', 'tasks.responsible_user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('work_volumes', 'tasks.target_id', 'work_volumes.id')
            ->leftjoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic', 'projects.name as project_name',
                'contractors.short_name as contractor_name', 'work_volumes.type', 'work_volumes.id as work_volume_id',
                'project_objects.address as object_address', 'tasks.*')
            ->orderBy('created_at', 'desc');
    }


    public function operation()
    {
        return $this->hasOne(MaterialAccountingOperation::class, 'id', 'target_id');
    }

    public function changing_fields()
    {
        return $this->hasMany(TaskChangingField::class);
    }

    public function update_taskable_fields()
    {
        $taskable = $this->taskable;

        foreach ($this->changing_fields as $field) {
            $name = $field->field_name;
            $taskable->$name = $field->value;
        }

        $taskable->save();
    }
}
