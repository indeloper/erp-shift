<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\TechAcc\Defects\Defects;
use App\Traits\NotificationGenerator;

class DefectObserver
{
    use NotificationGenerator;

    /**
     * Handle the defects "created" event.
     */
    public function created(Defects $defect): void
    {
        if (! $principal_mechanic = Group::find(47)->getUsers()->first()) {
            $this->generateNoPrincipleMechanicNotification();

            return;
        }

        $task = $defect->tasks()->create([
            'name' => 'Назначение исполнителя заявки на неисправность техники',
            'responsible_user_id' => $principal_mechanic->id,
            'status' => 26,
            'expired_at' => $this->addHours(8),
        ]);

        $this->generateDefectResponsibleAssignmentNotification($task);

        $defect->comments()->create([
            'comment' => "@user({$defect->user_id}) создал заявку на неисправность {$defect->created_at_formatted}.",
            'author_id' => $defect->user_id,
            'system' => 1,
        ]);
    }

    /**
     * Handle the defects "saved" event.
     */
    public function saved(Defects $defect): void
    {
        /*if ($defect->isInDiagnostics()) return;
        if (! $principal_mechanic = Group::find(47)->getUsers()->first()) { $this->generateNoPrincipleMechanicNotification(); return; }

        $task = $defect->tasks()->create([
            'name' => 'Назначение исполнителя заявки на неисправность техники',
            'responsible_user_id' => $principal_mechanic->id,
            'status' => 26,
            'expired_at' => $this->addHours(8)
        ]);

        $this->generateDefectResponsibleAssignmentNotification($task);

        $defect->comments()->create([
            'comment' => "@user({$defect->user_id}) создал заявку на неисправность {$defect->created_at_formatted}.",
            'author_id' => $defect->user_id,
            'system' => 1
        ]);*/
    }

    /**
     * Handle the defects "updated" event.
     */
    public function updated(Defects $defect): void
    {
        //
    }

    /**
     * Handle the defects "deleted" event.
     */
    public function deleted(Defects $defect): void
    {
        //
    }

    /**
     * Handle the defects "restored" event.
     */
    public function restored(Defects $defect): void
    {
        //
    }
}
