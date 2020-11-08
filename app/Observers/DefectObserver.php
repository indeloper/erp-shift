<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\Task;
use App\Models\TechAcc\Defects\Defects;
use App\Traits\NotificationGenerator;
use App\Traits\TimeCalculator;

class DefectObserver
{
    use TimeCalculator, NotificationGenerator;

    /**
     * Handle the defects "created" event.
     *
     * @param  Defects  $defect
     * @return void
     */
    public function created(Defects $defect)
    {
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
        ]);
    }

    /**
     * Handle the defects "saved" event.
     *
     * @param  Defects  $defect
     * @return void
     */
    public function saved(Defects $defect)
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
     *
     * @param  Defects  $defect
     * @return void
     */
    public function updated(Defects $defect)
    {
        //
    }

    /**
     * Handle the defects "deleted" event.
     *
     * @param  Defects  $defect
     * @return void
     */
    public function deleted(Defects $defect)
    {
        //
    }

    /**
     * Handle the defects "restored" event.
     *
     * @param  Defects  $defect
     * @return void
     */
    public function restored(Defects $defect)
    {
        //
    }
}
