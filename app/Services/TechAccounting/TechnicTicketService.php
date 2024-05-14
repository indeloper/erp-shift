<?php

namespace App\Services\TechAccounting;

use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Notification;
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use App\Services\SystemService;
use App\Traits\NotificationGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TechnicTicketService
{
    use NotificationGenerator;

    /**
     * @var TechnicTicketStatusCalculatorService
     */
    private $status_calculator;

    /**
     * @var SystemService
     */
    private $system_service;

    public $ticket_status_responsible_user_map = [
        1 => [
            'resp_rp_user_id',
        ],
        2 => [
            'process_resp_user_id',
        ],
        6 => [
            'recipient_user_id',
            'request_resp_user_id',
            'process_resp_user_id',
        ],
        5 => [
            'usage_resp_user_id',
        ],
        4 => [
            'process_resp_user_id',
        ],
        7 => [
            'usage_resp_user_id',
        ],
    ];

    public $responsible_user_task_status_map = [
        'resp_rp_user_id' => 28,
        'usage_resp_user_id' => 29,
        'process_resp_user_id' => 30,
        'request_resp_user_id' => 31,
        'recipient_user_id' => 32,
    ];

    public function __construct()
    {
        $this->status_calculator = new TechnicTicketStatusCalculatorService();
        $this->system_service = new SystemService();
    }

    public function createNewTicket($attributes)
    {
        DB::beginTransaction();

        $new_ticket = new OurTechnicTicket($attributes);
        $this->calculateTicketType($attributes, $new_ticket);
        $this->attachUsersToTicketWithId($attributes, $new_ticket);
        $this->updateTicketVehicles($attributes['vehicle_ids'] ?? [], $new_ticket);
        $new_ticket->refresh();

        if (Auth::user()->isInGroup(13, 19, 27, 8)) { //rps skips this step
            $new_ticket->status = $this->status_calculator->getIncreasedStatus($new_ticket);
            $new_ticket->save();

            if ($new_ticket->status == 5) {
                $this->createTaskAndNotificationForReadyForUsage($new_ticket);
            } else {
                $this->createTaskAndNotificationForWatingForProcessing($new_ticket);
            }
        } else {
            $responsible_rp_id = $new_ticket->users()->wherePivot('type', 1)->first()->id;

            $new_ticket->tasks()->create([
                'name' => 'Согласование заявки',
                'responsible_user_id' => $responsible_rp_id,
                'expired_at' => $this->addHours(24),
                'status' => 28,
            ]);

            $notification = new Notification([
                'name' => "Была создана заявка на {$new_ticket->our_technic->brand} {$new_ticket->our_technic->model}",
                'user_id' => $responsible_rp_id,
                'created_at' => now(),
                'target_id' => $new_ticket->id,
                'type' => 68,
            ]);
            $notification->additional_info = "\n".
                'Необходимо принять решение по заявке '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $new_ticket->id]);
            $notification->save();

        }

        DB::commit();
        $new_ticket->refresh();
        $new_ticket->loadMissing(['users', 'our_technic', 'sending_object', 'getting_object', 'comments.files']);

        return $new_ticket;
    }

    public function updateTicketStatus(OurTechnicTicket $ourTechnicTicket, $request, $auto = 0)
    {
        DB::beginTransaction();
        $result = $request['result'] ?? null;
        $result = $request['acceptance'] ?? $result;
        if ($result != 'update') {
            if ($auto) {
                $ourTechnicTicket->tasks()->where('status', 28)->get()->each(function ($task) {
                    $task->solve_n_notify();
                });
            } else {
                $this->closeResponsibleUserTask($ourTechnicTicket, $request);
            }
        } else {
            $old_vehicle = $ourTechnicTicket->vehicles->first()->id;
            $old_sender = $ourTechnicTicket->users()->ofType('request_resp_user_id')->activeResp()->first()->id;
            $old_recipient = $ourTechnicTicket->users()->ofType('recipient_user_id')->activeResp()->first()->id;

            $this->updateTicket($ourTechnicTicket, $request);
            $changes = 'Были изменены следующие поля: ';
            foreach ($ourTechnicTicket->getChanges() as $field => $value) {
                if ($field == 'sending_from_date') {
                    $changes .= 'Дата начала отправки, ';
                } elseif ($field == 'sending_to_date') {
                    $changes .= 'Дата окончания отправки, ';
                } elseif ($field == 'getting_from_date') {
                    $changes .= 'Дата начала получения, ';
                } elseif ($field == 'getting_to_date') {
                    $changes .= 'Дата окончания получения, ';
                } elseif ($field == 'our_technic_id') {
                    $changes .= 'Техническое устройство, ';
                }
            }
            if ($old_recipient != $request['recipient_user_id']) {
                $changes .= 'Ответственынй за получение, ';

            }
            if ($old_sender != $request['ticket_resp_user_id']) {
                $changes .= 'Ответственный за отправку, ';
            }
            if ($old_vehicle != $request['vehicle_ids'][0]) {
                $changes .= 'Транспорт, ';
            }

            $ourTechnicTicket->comments()->create([
                'comment' => substr($changes, 0, -2),
                'author_id' => Auth::id(),
                'system' => 1,
            ]);
        }

        if ($ourTechnicTicket->status == 1) {
            $ourTechnicTicket->status = $this->status_calculator->getIncreasedStatus($ourTechnicTicket, $result);
            if ($result == 'confirm') {
                $this->attachUsersToTicketWithId($request, $ourTechnicTicket);
                if ($auto) {
                    $ourTechnicTicket->comments()->create([
                        'comment' => 'Заявка была согласована автоматически',
                        'author_id' => 0,
                        'system' => 1,
                    ]);
                } else {
                    $ourTechnicTicket->comments()->create([
                        'comment' => 'Заявка была согласована',
                        'author_id' => Auth::id(),
                        'system' => 1,
                    ]);
                }

                $this->generateTicketAcceptedNotification($ourTechnicTicket);

                if ($ourTechnicTicket->status == 5) {
                    $this->createTaskAndNotificationForReadyForUsage($ourTechnicTicket);
                } else {
                    $this->createTaskAndNotificationForWatingForProcessing($ourTechnicTicket);
                }
            } else {
                $this->generateFailureComment($ourTechnicTicket, $request, $result);
            }
        } elseif (in_array($ourTechnicTicket->status, [2, 4])) {
            $ourTechnicTicket->status = $this->status_calculator->getIncreasedStatus($ourTechnicTicket, $result);
            if ($result == 'confirm') {
                $ourTechnicTicket->comments()->create([
                    'comment' => 'Заявка была обработана'.$this->getCommentFromRequest($request),
                    'author_id' => Auth::id(),
                    'system' => 1,
                ]);
                $this->updateTicket($ourTechnicTicket, $request);
                $this->createTaskAndNotificationForMoving($ourTechnicTicket);

            } else {
                $this->generateFailureComment($ourTechnicTicket, $request, $result);
            }
        } elseif ($ourTechnicTicket->status == 5) {
            $ourTechnicTicket->status = $this->status_calculator->getIncreasedStatus($ourTechnicTicket, $result);

            $ourTechnicTicket->comments()->create([
                'comment' => 'Отметка о начале использования техники',
                'author_id' => Auth::id(),
                'system' => 1,
            ]);

            $ourTechnicTicket->save();
            (new TechnicTicketReportService())->checkAndCreateTaskForUserId($ourTechnicTicket->users()->ofType('usage_resp_user_id')->first()->id);
        } elseif ($ourTechnicTicket->status == 6) {
            if ($result == 'rollback') {
                $ourTechnicTicket->comments()->create([
                    'comment' => 'Перемещение не удалось, заявка возвращена '.$this->getCommentFromRequest($request),
                    'author_id' => Auth::id(),
                    'system' => 1,
                ]);

                $ourTechnicTicket->status = $this->status_calculator->getIncreasedStatus($ourTechnicTicket, $result);
                $ourTechnicTicket->tasks()->whereIn('status', [31, 32])->get()->each(function ($task) {
                    $task->solve_n_notify();
                });
                $this->createTaskAndNotificationForWatingForProcessing($ourTechnicTicket);
            } elseif ($result == 'confirm') {
                $comment = $ourTechnicTicket->comments()->create([
                    'comment' => 'Перемещение техники подтверждено'.$this->getCommentFromRequest($request),
                    'author_id' => Auth::id(),
                    'system' => 1,
                ]);
                if (isset($request['file_ids'])) {
                    $comment->documents()->saveMany(FileEntry::find($request['file_ids']));
                }

                if ($this->isTechnicMovingComplete($ourTechnicTicket)) {
                    $ourTechnicTicket->status = $this->status_calculator->getIncreasedStatus($ourTechnicTicket, $result);
                    $ourTechnicTicket->our_technic->start_location_id = $ourTechnicTicket->getting_object->id;
                    $ourTechnicTicket->push();

                    if ($ourTechnicTicket->status == 5) {
                        $this->createTaskAndNotificationForReadyForUsage($ourTechnicTicket);
                    }
                }
            }
        }

        $ourTechnicTicket->save();
        $ourTechnicTicket->loadAllMissingRelations();

        DB::commit();

        return $ourTechnicTicket;
    }

    public function collectShortTicketsData(array $request)
    {
        $paginated = OurTechnicTicket::with('users', 'reports')->filter($request)->permissionCheck()->WithOutClosed()->paginate(15);
        $empty_model = OurTechnicTicket::getModel();
        $authed_user = auth()->user();
        $authed_rp = '';
        if (in_array($authed_user->group_id, Group::PROJECT_MANAGERS)) {
            $authed_rp = $authed_user;
        }

        return [
            'data' => [
                'tickets' => $paginated->items(),
                'project_managers' => Group::PROJECT_MANAGERS,
                'main_logist' => User::find((new User())->main_logist_id),
                'ticketsCount' => $paginated->total(),
                'statuses' => $empty_model->statuses,
                'authed_rp' => $authed_rp,
                'specializations' => $empty_model->specializations,
            ]];
    }

    /**
     * @return OurTechnicTicket $new_ticket
     */
    public function calculateTicketType($attributes, OurTechnicTicket $new_ticket)
    {
        $usage_date = $attributes['usage_from_date'] ?? null;
        $sending_date = $attributes['getting_to_date'] ?? null;

        if ($usage_date and $sending_date) {
            $new_ticket->type = 3;
        } else {
            $new_ticket->type = $usage_date ? 1 : 2;
        }

        return $new_ticket;
    }

    public function attachUsersToTicketWithId($attributes, OurTechnicTicket $new_ticket)
    {
        $this->makeSureTicketIsSaved($new_ticket);
        $user_types = (new User())->ticket_responsible_types;
        foreach ($user_types as $type_id => $type_name) {
            $type_name = ($type_name == 'request_resp_user_id') ? 'ticket_resp_user_id' : $type_name;
            if (isset($attributes[$type_name])) {
                if (DB::table('our_technic_ticket_user')->where('tic_id', $new_ticket->id)->where('type', $type_id)->count()) {
                    $old_user_ids = DB::table('our_technic_ticket_user')
                        ->where('tic_id', $new_ticket->id)->where('type', $type_id)
                        ->get()->pluck('user_id');
                    $new_ticket->tasks()
                        ->whereIn('responsible_user_id', $old_user_ids)
                        ->whereIn('status', [$this->responsible_user_task_status_map[(($type_name == 'ticket_resp_user_id') ? 'request_resp_user_id' : $type_name)]])
                        ->where('is_solved', 0)
                        ->update(['responsible_user_id' => $attributes[$type_name]]);
                    DB::table('our_technic_ticket_user')->where('tic_id', $new_ticket->id)->where('type', $type_id)->delete();
                }
                $new_ticket->users()->attach($attributes[$type_name], ['type' => $type_id]);
            }
        }
        if (! $new_ticket->users()->where('type', 5)->exists()) {
            $new_ticket->users()->attach(Auth::id(), ['type' => 5]);
        }
    }

    public function makeSureTicketIsSaved(OurTechnicTicket $new_ticket)
    {
        if (! $new_ticket->id) {
            $new_ticket->save();
        }
    }

    public function createTaskAndNotificationForReadyForUsage(OurTechnicTicket $ourTechnicTicket): void
    {
        $user_types = $this->ticket_status_responsible_user_map[$ourTechnicTicket->status];

        foreach ($user_types as $user_type) {
            $usage_responsible_user = $ourTechnicTicket->users()->ofType($user_type)->first();

            $ourTechnicTicket->tasks()->create([
                'name' => 'Подтверждение начала использования',
                'responsible_user_id' => $usage_responsible_user->id,
                'expired_at' => $this->addHours(24),
                'status' => $this->responsible_user_task_status_map[$user_type],
            ]);

            $notification = new Notification();
            $notification->save();
            $notification->additional_info = "\n".
                'Необходимо подтвердить начало использования: '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notification->update([
                'name' => "Техника: {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model} - готова к использованию",
                'user_id' => $usage_responsible_user->id,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 69,
            ]);
        }
    }

    public function createTaskAndNotificationForWatingForProcessing(OurTechnicTicket $ourTechnicTicket): void
    {
        $logist_id = $ourTechnicTicket->getResponsibleType('process_resp_user_id')->id;

        $ourTechnicTicket->tasks()->create([
            'name' => 'Обработка заявки на технику',
            'responsible_user_id' => $logist_id,
            'expired_at' => $this->addHours(24),
            'status' => 30,
        ]);

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = "\n".
            'Ссылка: '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
        $notification->update([
            'name' => "Необходимо обработать заявку на {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
            'user_id' => $logist_id,
            'created_at' => now(),
            'target_id' => $ourTechnicTicket->id,
            'type' => 70,
        ]);
    }

    public function createTaskAndNotificationForMoving(OurTechnicTicket $ourTechnicTicket): void
    {
        $sending_responsible_user = $ourTechnicTicket->users()->ofType('request_resp_user_id')->first();

        $this->generateTicketProcessedNotification($ourTechnicTicket);

        $ourTechnicTicket->tasks()->create([
            'name' => 'Подтверждение перемещения',
            'responsible_user_id' => $sending_responsible_user->id,
            'expired_at' => $this->addHours(24),
            'status' => 31,
        ]);

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = "\n".
            'Ссылка: '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
        $notification->update([
            'name' => "Необходимо обработать заявку на {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
            'user_id' => $sending_responsible_user->id,
            'created_at' => now(),
            'target_id' => $ourTechnicTicket->id,
            'type' => 71,
        ]);

        $getting_responsible_user = $ourTechnicTicket->users()->ofType('recipient_user_id')->first();

        $ourTechnicTicket->tasks()->create([
            'name' => 'Подтверждение перемещения',
            'responsible_user_id' => $getting_responsible_user->id,
            'expired_at' => $this->addHours(24),
            'status' => 32,
        ]);

        $notification = new Notification();
        $notification->save();
        $notification->additional_info = "\n".
            'Ссылка: '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
        $notification->update([
            'name' => "Необходимо обработать заявку на {$ourTechnicTicket->our_technic->brand} {$ourTechnicTicket->our_technic->model}",
            'user_id' => $getting_responsible_user->id,
            'created_at' => now(),
            'target_id' => $ourTechnicTicket->id,
            'type' => 72,
        ]);
    }

    public function closeResponsibleUserTask(OurTechnicTicket $ourTechnicTicket, $request)
    {
        $curr_types = $this->ticket_status_responsible_user_map[$ourTechnicTicket->status];

        if (isset($request['task_status'])) {
            $ourTechnicTicket->tasks()->where('status', $request['task_status'])->get()->each(function ($task) use ($request, $ourTechnicTicket) {
                $task->final_note = $request['final_note'] ?? '';
                $task->solve_n_notify();
                $this->generateMovingNotificationsIfNeeded($task, $ourTechnicTicket);
            });
        } else {
            foreach ($curr_types as $curr_type) {
                if ($this->isAuthIsTicketRespOfType($ourTechnicTicket, $curr_type)) {
                    $ourTechnicTicket->tasks()->where('status', $this->responsible_user_task_status_map[$curr_type])->get()->each(function ($task) use ($request) {
                        $task->final_note = $request['final_note'] ?? '';
                        $task->solve_n_notify();
                    });
                }
                break;
            }
        }
    }

    public function isTechnicMovingComplete(OurTechnicTicket $ourTechnicTicket): bool
    {
        return $ourTechnicTicket->tasks()->whereIn('status', [31, 32])->where('is_solved', 0)->count() == 0;
    }

    /**
     * @param  $curr_type
     */
    public function isAuthIsTicketRespOfType(OurTechnicTicket $ourTechnicTicket, $curr_types): bool
    {
        $curr_types = collect($curr_types);
        $authed = false;
        foreach ($curr_types as $curr_type) {
            $authed = (in_array(Auth::id(), $ourTechnicTicket->users()->ofType($curr_type)->pluck('id')->toArray()));
            if (Auth::user()->isProjectManager()) {
                $authed = true;
            }
            if ($authed) {
                break;
            }
        }

        return $authed;
    }

    public function updateTicketVehicles($vehicle_ids, OurTechnicTicket $ticket)
    {
        $ticket->vehicles()->sync(collect($vehicle_ids));

        return $ticket;
    }

    public static function makeTtn(OurTechnicTicket $ourTechnicTicket, Request $data)
    {
        $ourTechnicTicket->load('our_technic.category_characteristics');

        $data->merge(['id' => $ourTechnicTicket->id]);
        $data->merge(['cargo' => $ourTechnicTicket->our_technic->name]);
        $data->merge(['object_from' => $ourTechnicTicket->sending_object]);
        $data->merge(['object_to' => $ourTechnicTicket->getting_object]);

        // dd($data->all());
        return view('tech_accounting.reports.ttn', $data->all());
    }

    public function getCommentFromRequest($request): string
    {
        return isset($request['comment']) ? ' с комментарием: '.$request['comment'] : '';
    }

    public function generateFailureComment(OurTechnicTicket $ourTechnicTicket, $request, $result): void
    {
        $this->generateTicketFailureNotification($ourTechnicTicket, $result);

        $ourTechnicTicket->comments()->create([
            'comment' => 'Заявка была '.($result == 'hold' ? 'удержана' : 'отклонена').$this->getCommentFromRequest($request),
            'author_id' => Auth::id(),
            'system' => 1,
        ]);
    }

    /**
     * @return OurTechnicTicket
     */
    public function updateTicket(OurTechnicTicket $ourTechnicTicket, $request)
    {
        if (($ourTechnicTicket->usage_from_date or $ourTechnicTicket->usage_to_date) and isset($request['getting_to_date'])) {
            if ($ourTechnicTicket->usage_from_date < Carbon::parse($request['getting_to_date'])) {
                $usage_days = Carbon::parse($ourTechnicTicket->usage_to_date)->diffInDays(Carbon::parse($ourTechnicTicket->usage_from_date));
                $ourTechnicTicket->usage_to_date = Carbon::parse($request['getting_to_date'])->addDays($usage_days);
                $ourTechnicTicket->usage_from_date = $request['getting_to_date'];
            }
        }

        if (isset($request['vehicle_ids'])) {
            $this->updateTicketVehicles($request['vehicle_ids'], $ourTechnicTicket);
        }
        $this->attachUsersToTicketWithId($request, $ourTechnicTicket);
        $ourTechnicTicket->update($request);
        $ourTechnicTicket->save();

        return $ourTechnicTicket;
    }
}
