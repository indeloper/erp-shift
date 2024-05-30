<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class QuestionnaireSend extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($task, $contact, $user)
    {
        $this->task = $task;
        $this->user = User::where('users.id', $user->id)
            ->leftJoin('groups', 'groups.id', '=', 'users.group_id')
            ->select('users.id', DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic) AS full_name'), 'users.birthday',
                'users.email', 'users.person_phone', 'users.work_phone', 'users.status', 'groups.name as group_name')
            ->first();
        $this->contact = $contact;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->view('emails.questionnaire', [
            'task' => $this->task,
            'user' => $this->user,
            'contact' => $this->contact,
        ]);
    }
}
