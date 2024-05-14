<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectEvents
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function projectCreated(Project $project)
    {
        //        Log::info('Passing into event');
        $project_creator = User::find($project->user_id);
        $ceos = User::whereIn('id', [6, 9, 27])->get();
        //        Log::info('initiator: ' . $project_creator->first_name . ' to whom: ' . $ceos->first()->last_name . ' count_ceos: ' . $ceos->count());
        if ($project_creator and $ceos->count() > 0) {
            DB::beginTransaction();
            foreach ($ceos as $ceo) {
                //                Log::info('creating notification');
                $notification = new Notification();
                $notification->save();
                $notification->additional_info = "\r\nЗаказчик: ".$project->contractor_name
                    ."\r\nНазвание объекта: ".$project->object->name
                    ."\r\nАдрес объекта: ".$project->object->address."\r\n".
                    'Чтобы просмотреть проект, перейдите по ссылке: '.route('projects::card', $project->id);
                $notification->update([
                    'name' => 'Был создан проект "'.$project->name.'". Автор: '.$project_creator->long_full_name,
                    'status' => 1,
                    'user_id' => $ceo->id,
                    'contractor_id' => $project->contractor_id,
                    'project_id' => $project->id,
                    'object_id' => $project->object_id,
                    'type' => 45,
                ]);

            }
            DB::commit();
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
