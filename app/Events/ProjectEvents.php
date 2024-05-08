<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use App\Notifications\Project\NewProjectCreationNotice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
        $ceos = User::whereIn('id', [6, 9, 27])->get()->pluck('id')->toArray();
//        Log::info('initiator: ' . $project_creator->first_name . ' to whom: ' . $ceos->first()->last_name . ' count_ceos: ' . $ceos->count());
        if ($project_creator and $ceos->count() > 0) {
            NewProjectCreationNotice::send(
                $ceos,
                [
                    'name' => 'Был создан проект "' . $project->name . '". Автор: ' . $project_creator->long_full_name,
                    'additional_info' => "\r\nЗаказчик: " . $project->contractor_name .
                        "\r\nНазвание объекта: " . $project->object->name .
                        "\r\nАдрес объекта: " . $project->object->address .
                        "\r\nЧтобы просмотреть проект, перейдите по ссылке: ",
                    'url' => route('projects::card', $project->id),
                    'contractor_id' => $project->contractor_id,
                    'project_id' => $project->id,
                    'object_id' => $project->object_id,
                    'status' => 1,
                ]
            );
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
