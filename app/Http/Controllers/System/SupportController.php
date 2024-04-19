<?php

namespace App\Http\Controllers\System;

use App\Domain\Enum\NotificationType;
use App\Events\NotificationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupportRequests\SupportMailRequest;
use App\Models\{FileEntry, Group, SupportMail, SupportMailFile, Task, Notification};
use App\Services\System\Reports\SupportTaskExport;
use App\Traits\TimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{Auth, DB, File, Storage, URL};

class SupportController extends Controller
{
    use TimeCalculator;

    public function index(Request $request)
    {
        $support_tickets = SupportMail::basic($request);

        return view('support.index', [
            'support_tickets' => $support_tickets->paginate(10),
        ]);
    }

    public function support_send_mail(SupportMailRequest $request)
    {
        DB::beginTransaction();

        $support = SupportMail::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => (Auth::user()->id == 1 and $request->user_id) ? $request->user_id : Auth::user()->id,
            'page_path' => $request->page_path,
            'status' => 'new',
        ]);

        $images_paths = [];
        $mimes = [];
        $url = URL::to('/');
        if ($request->images) {
            foreach ($request->images as $image) {
                $file = new SupportMailFile();

                $file->support_mail_id = $support->id;

                $mime = $image->getClientOriginalExtension();
                $file_name = Carbon::now()->format('d-m-y__H:i') . '_' . md5(uniqid()) . '.' . $mime;

                Storage::disk('support_mail_image')->put($file_name, File::get($image));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $image->getSize(),
                    'mime' => $image->getClientMimeType(),
                    'original_filename' => $image->getClientOriginalName(),
                    'user_id' => Auth::user()->id
                ]);

                $images_paths[] = $url . '/storage/img/support_mail_images/' . $file_name;
                $name = $image->getClientOriginalName();
                $mimes[] = $mime;
                $names[] = $name;

                $file->original_name = $name;
                $file->path = '/storage/img/support_mail_images/' . $file_name;

                $file->save();
            }
        }

        DB::commit();
        if (Auth::user()->id != 1) {
            $to      = '911@sk-gorod.com';
            $subject = 'SK-HELP  NOTIFY';
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= 'From: dev@sk-gorod.com';
            $message = '<h1>' . $support->title . '</h1>';
            $message .= '<p style="font-size: 14px;">' . $support->description . '</p>';
            $message .= '<p>Сообщение пришло из ' . $support->page_path . '</p>';
            $message .= '<p>Отправитель: ' . Auth::user()->long_full_name . '</p>';

            foreach ($images_paths as $key => $file_url) {
                if (in_array($mimes[$key], ['png', 'jpg', 'jpeg', 'gif'])) {
                    $message .= '<img src="' . $file_url . '"><br>';
                } else {
                    $message .= 'Файл "' . $names[$key] .'". ' . $file_url . '<br>';
                }
            }

            mail($to, $subject, $message, $headers);
            mail('dev@sk-gorod.com', $subject, $message, $headers);

            dispatchNotify(
                1,
                'Была создана заявка в тех. поддержке',
                'Была создана заявка в тех. поддержке',
                NotificationType::SUPPORT_TICKET_STATUS_CHANGE_NOTIFICATION,
                [
                    'status' => 2,
                    'additional_info' => ' от ' . Auth::user()->full_name . '. Ссылка на тех поддержку: ' . route('support::index')
                ]
            );
        }

        return back();
    }

    public function update_ticket_async(Request $request)
    {
        DB::beginTransaction();

        $ticket = SupportMail::findOrFail($request->ticket_id);
        $oldStatus = $ticket->status;
        $ticket->status = $request->status ? $request->status : 'new';
        $ticket->result_description = $request->result_description;
        $ticket->estimate = $request->estimate ?? $ticket->estimate;

        $ticket->save();

        $this->sendNotificationAfterUpdate($oldStatus, $ticket);

        if ($ticket->status == 'matching') {
            $task = Task::create([
                'name' => 'Согласование дополнительных работ',
                'description' => '<p>Тема: ' . $ticket->title . '. </p><p>Описание: ' . $ticket->description . '.  </p><p>Автор: ' . $ticket->sender->full_name . '</p>' . '<p>Необходимое время: ' . $ticket->estimate . ' ч. </p>' . ($ticket->result_description ? ('</p>' . '<p>Комментарий от отдела ИТ: ' . $ticket->result_description . '</p>') : ''),
                'responsible_user_id' => Group::find(5/*3*/)->getUsers()->first()->id,
                'status' => 1,
                'target_id' => $ticket->id,
                'expired_at' => $this->addHours(48)
            ]);

            dispatchNotify(
                $task->responsible_user_id,
                'Новая задача «' . $task->name . '»',
                'Новая задача «' . $task->name . '»',
                NotificationType::ADDITIONAL_WORKS_APPROVAL_TASK_NOTIFICATION,
                [
                    'task_id' => $task->id,
                    'additional_info' => ' Ссылка на задачу: ' . $task->task_route()
                ]
            );
        }

        DB::commit();

        return $ticket;
    }

    public function update_solved_at(Request $request)
    {
        DB::beginTransaction();

        $ticket = SupportMail::findOrFail($request->ticket_id);
        $previousDate = $ticket->solved_at;
        $ticket->solved_at = Carbon::parse($request->solved_at)->format('d.m.Y H:i');
        $ticket->save();

        if ($previousDate == null) {
            $this->createFirstNotification($ticket);
        } else {
            $this->createSecondNotifications($ticket, $previousDate);
        }

        DB::commit();

        return $ticket;
    }

    public function task_agreed(Request $request, $task_id)
    {
        DB::beginTransaction();

        $task = Task::findOrFail($task_id);
        $task->final_note = $request->result == 'accept' ? 'Согласовано. <b>' . $request->final_note . '</b><br> Необходимо сформировать счёт.' : 'Отклонено. <b>' . $request->final_note . '</b>';
        $task->is_solved = 1;
        $task->save();

        $ticket = SupportMail::find($task->target_id);
        $oldStatus = $ticket->status;
        $ticket->status = $request->result;
        $ticket->save();

        $this->sendNotificationAfterUpdate($oldStatus, $ticket);

        DB::commit();
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . 'dev@sk-gorod.com';
        $message = $task->description . '<p>' . $task->final_note . '</p>';
        $result = mail('dev@sk-gorod.com', 'СК согласование', $message, $headers);

        return redirect()->route('tasks::index');
    }

    public function createFirstNotification($ticket)
    {
        $notificationName = "Ваша заявка «{$ticket->title}», ID: {$ticket->id} получила срок приблизительного исполнения." .
            " Предполагаемая дата реализации: {$ticket->solved_at}.";

        dispatchNotify(
            $ticket->user_id,
            $notificationName,
            '',
            NotificationType::SUPPORT_TICKET_APPROXIMATE_DUE_DATE_CHANGE_NOTIFICATION
        );

        $this->sendEmailToITDepartment($ticket->user_id, $notificationName);
    }

    public function createSecondNotifications($ticket, $previousDate)
    {
        $was = Carbon::parse($previousDate);
        $will = Carbon::parse($ticket->solved_at);
        $notificationName = ($will->gt($was) ? 'К сожалению, с' : 'С') .
                "рок исполнения вашей заявки «‎{$ticket->title}», ID: {$ticket->id} изменился." .
            " Предыдущая дата: {$previousDate}, новая дата: {$ticket->solved_at}." .
            ($will->gt($was) ? ' Приносим извинения!' : '');

        dispatchNotify(
            $ticket->user_id,
            $notificationName,
            '',
            NotificationType::SUPPORT_TICKET_APPROXIMATE_DUE_DATE_CHANGE_NOTIFICATION
        );

        $this->sendEmailToITDepartment($ticket->user_id, $notificationName);
    }

    public function sendNotificationAfterUpdate($oldStatus, $ticket): void
    {
        switch (true) {
            case (in_array($oldStatus, ['new', 'accept', 'decline']) and $ticket->status == 'in_work'):
                $this->createMoveToWorkFromNewNotification($ticket);
                break;
            case $ticket->status == 'matching':
                $this->createMatchingNotification($ticket);
                break;
            case $ticket->status == 'accept':
                $this->createAcceptNotification($ticket);
                break;
            case $ticket->status == 'decline':
                $this->createDeclineNotification($ticket);
                break;
            case $ticket->status == 'resolved':
                $this->createResolvedNotification($ticket);
                break;
            case $ticket->status == 'development':
                $this->createDevelopmentNotification($ticket);
                break;
            case $ticket->status == 'check':
                $this->createCheckNotification($ticket);
                break;
        }
    }

    public function createMoveToWorkFromNewNotification($ticket)
    {
        $notificationName = "Ваша заявка «{$ticket->title}», ID: {$ticket->id} принята в работу!";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

    public function createMatchingNotification($ticket)
    {
        $notificationName = "Ваша заявка «{$ticket->title}», ID: {$ticket->id} была отправлена на согласование!";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

    public function createAcceptNotification($ticket)
    {
        $notificationName = "Ваша заявка «{$ticket->title}», ID: {$ticket->id} была согласована!" .
            " В скором времени мы приступим к её исполнению!";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

    public function createDeclineNotification($ticket)
    {
        $notificationName = "Ваша заявка «{$ticket->title}», ID: {$ticket->id} была отклонена в результате согласования!";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

    public function createResolvedNotification($ticket)
    {
        $notificationName = "Ваша заявка «{$ticket->title}», ID: {$ticket->id} была закрыта!" .
            " Наши специалисты в скором времени свяжутся, чтобы сообщить вам об изменениях!";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

    public function createDevelopmentNotification($ticket)
    {
        $notificationName = $ticket->sender->full_name .
            ", наши специалисты приступили к реализации вашей задачи «{$ticket->title}», ID: {$ticket->id}." .
            " По окончанию работ наши специалисты свяжутся с вами.";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

    public function createCheckNotification($ticket)
    {
        $notificationName = $ticket->sender->full_name . ", наши специалисты рассказали Вам про реализованный функционал задачи «{$ticket->title}», ID: {$ticket->id}" .
            " и ждут пока вы проверите ее реализацию. По любым вопросам вы можете обращаться к нашим сотрудникам из технической поддержки.";

        $this->sendSupportTicketStatusNotification($ticket->user_id, $notificationName);
    }

//    public function makeOtherNotifications($ticket, $notification): void
//    {
//        $this->sendEmailToITDepartmentOld($notification);
//    }
//
//    public function sendEmailToITDepartmentOld($notification)
//    {
//        $headers  = 'MIME-Version: 1.0' . "\r\n";
//        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//        $headers .= 'From: ' . 'dev@sk-gorod.com';
//        $message = "Пользователь {$notification->user->long_full_name} получил(а) уведомление о заявке: " . '<p>' . $notification->name . '</p>';
//        $result = mail('dev@sk-gorod.com', 'СК изменение заявки', $message, $headers);
//    }
    public function sendSupportTicketStatusNotification($userId, $notificationName)
    {
        dispatchNotify(
            $userId,
            $notificationName,
            '',
            NotificationType::SUPPORT_TICKET_STATUS_CHANGE_NOTIFICATION
        );

        $this->sendEmailToITDepartment($userId, $notificationName);
    }

    public function sendEmailToITDepartment($userId, $notificationName)
    {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . 'dev@sk-gorod.com';
        $message = "Пользователь с id {$userId} получил(а) уведомление о заявке: " . '<p>' . $notificationName . '</p>';
        $result = mail('dev@sk-gorod.com', 'СК изменение заявки', $message, $headers);
    }

    public function report()
    {
        abort_if(!in_array(Auth::user()->id, [1, 5, 6, 13]), '403');

        $report = new SupportTaskExport();

        return $report->export();
    }

    public function updateLink(Request $request)
    {
        abort_if(auth()->id() != 1, Response::HTTP_FORBIDDEN);
        DB::beginTransaction();

        $ticket = SupportMail::findOrFail($request->ticket_id);
        $ticket->update(['gitlab_link' => $request->gitlab_link]);

        DB::commit();

        return response()->json(true);
    }
}
