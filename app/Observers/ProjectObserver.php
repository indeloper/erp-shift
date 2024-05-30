<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\User;
use App\Traits\NotificationGenerator;

class ProjectObserver
{
    use NotificationGenerator;

    /**
     * Handle the project "created" event.
     */
    public function created(Project $project): void
    {
        //
    }

    /**
     * Handle the project "updated" event.
     */
    public function updated(Project $project): void
    {
        $project->generateAction('update');

        if (in_array('time_responsible_user_id', array_keys($project->getChanges()))) {
            $oldResponsible = $project->getOriginal('time_responsible_user_id');
            $newResponsible = $project->time_responsible_user_id;

            switch (true) {
                case boolval(! empty($oldResponsible) and ! empty($newResponsible)):
                    $this->generateNewProjectTimeResponsibleUserAssignmentNotification($newResponsible, $project);
                    $this->generateProjectTimeResponsibleUserDepositionNotification($oldResponsible, $project);
                    $movedTasks = User::find($oldResponsible)->tasks()->whereIn('status', [40, 41])->update(['responsible_user_id' => $newResponsible]);
                    break;
                case boolval(empty($oldResponsible) and ! empty($newResponsible)):
                    $this->generateNewProjectTimeResponsibleUserAssignmentNotification($newResponsible, $project);
                    break;
                case boolval(! empty($oldResponsible) and empty($newResponsible)):
                    $this->generateProjectTimeResponsibleUserDepositionNotification($oldResponsible, $project);
                    break;
            }
        }
    }

    /**
     * Handle the project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        //
    }

    /**
     * Handle the project "restored" event.
     */
    public function restored(Project $project): void
    {
        //
    }

    /**
     * Handle the project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        //
    }
}
