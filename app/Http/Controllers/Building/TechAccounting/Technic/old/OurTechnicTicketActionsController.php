<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\TechAcc\OurTechnicTicket;
use App\Services\TechAccounting\TechnicTicketService;
use App\Traits\NotificationGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OurTechnicTicketActionsController extends Controller
{
    use NotificationGenerator;

    // 1338
    public function close(Request $request, OurTechnicTicket $ourTechnicTicket)
    {
        $this->authorize('close', $ourTechnicTicket);

        DB::beginTransaction();

        $ourTechnicTicket->close();

        $ourTechnicTicket->comments()->create([
            'comment' => 'Отметка об окончании использования техники. Комментарий пользователя: '.$request->comment,
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => $ourTechnicTicket->loadAllMissingRelations(),
        ]);
    }

    // 1339
    public function request_extension(Request $request, OurTechnicTicket $ourTechnicTicket)
    {
        $this->authorize('request_extension', $ourTechnicTicket);

        DB::beginTransaction();

        $comment = $ourTechnicTicket->comments()->create([
            'comment' => 'Запрос продления использования техники. Комментарий пользователя: '.$request->comment,
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);

        $task = $ourTechnicTicket->tasks()->create([
            'name' => 'Запрос продления использования техники.',
            'description' => 'Предыдущая дата: '.Carbon::parse($ourTechnicTicket->usage_to_date)->format('d.m.Y').'. Новая дата: '.$request->usage_to_date.'. Комментарий: '.$comment->comment.'.',
            'responsible_user_id' => $ourTechnicTicket->users()->wherePivot('type', 1)->first()->id,
            'expired_at' => Carbon::now()->addHours(8),
            'status' => 27,
        ]);

        $task->changing_fields()->create([
            'field_name' => 'usage_to_date',
            'value' => $request->usage_to_date,
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => $ourTechnicTicket->loadAllMissingRelations(),
        ]);
    }

    // 1340, 1341
    public function agree_extension(Request $request, OurTechnicTicket $ourTechnicTicket)
    {
        $this->authorize('agree_extension', $ourTechnicTicket);

        DB::beginTransaction();

        $task = $ourTechnicTicket->tasks()->where('status', 27)->whereIsSolved(0)->first();

        if ($request->agree) {
            $task->final_note = $request->final_note;
            $task->result = 1;
            $task->update_taskable_fields();
            $notification = 'Продление до '.$task->changing_fields->where('field_name', 'usage_to_date')->first()->value." по заявке № $ourTechnicTicket->id согласовано.";
            $notify = new Notification([
                'name' => $notification,
                'user_id' => $ourTechnicTicket->users()->ofType('usage_resp_user_id')->first()->id,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 82,
            ]);
            $notify->additional_info = "\n".'Ссылка на заявку: '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notify->save();
            $this->generateOurTechnicTicketUseExtensionNotifications($ourTechnicTicket);
        } else {
            $task->final_note = $request->final_note;
            $task->result = 2;
            $notification = 'Продление до '.$task->changing_fields->where('field_name', 'usage_to_date')->first()->value." по заявке № $ourTechnicTicket->id отклонено с комментарием: $task->final_note";
            $notify = new Notification([
                'name' => $notification,
                'user_id' => $ourTechnicTicket->users()->ofType('usage_resp_user_id')->first()->id,
                'created_at' => now(),
                'target_id' => $ourTechnicTicket->id,
                'type' => 83,
            ]);
            $notify->additional_info = "\n".'Ссылка на заявку: '.route('building::tech_acc::our_technic_tickets.index', ['ticket_id' => $ourTechnicTicket->id]);
            $notify->save();
        }

        $task->solve();
        $ourTechnicTicket->refresh();
        $task->refresh();

        $ourTechnicTicket->comments()->create([
            'comment' => ($task->get_result.' Комментарий: '.$task->final_note),
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);

        DB::commit();

        return redirect()->back();
    }

    public function make_ttn(Request $request, OurTechnicTicket $ourTechnicTicket)
    {
        return TechnicTicketService::makeTtn($ourTechnicTicket, $request);
    }
}
