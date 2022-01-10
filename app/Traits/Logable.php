<?php

namespace App\Traits;

use App\Models\ActionLog;

trait Logable
{
    /**
     * Main idea - store changes that happened to models
     * Better to use with three keys in array:
     * 1. event - what happened with model? (create/edit/etc)
     * 2. new values - values, given to model
     * 3. old values - values, that model had before
     */
    public function logs()
    {
        return $this->morphMany(ActionLog::class, 'logable');
    }

    /**
     * Method collect data for actions log
     * and store it to relation.
     * @param string $event
     */
    public function generateAction(string $event = 'create')
    {
        $action = $this->collectDataForActionLogs($event);
        $this->logs()->create($action);
    }

    /**
     * Method collect model data for actions log
     * and store it to relation.
     * @param string $event
     */
    public function generatePastAction(string $event = 'create')
    {
        $action = $this->collectOriginDataForActionLogs($event);
        $this->logs()->create($action);
    }

    /**
     * Function parse model changes into array.
     * Special for ActionLog
     * @param string $event
     * @return array
     */
    public function collectDataForActionLogs(string $event = 'create'): array
    {
        $action = [];
        $action['user_id'] = auth()->id() ?? 1;

        $new_values = $this->getDirty();
        $old_values = [];

        foreach ($new_values as $field => $value) {
            $old_values[$field] = $this->getOriginal($field);
        }

        $action['actions'] = [
            'event' => $event,
            'new_values' => $new_values,
            'old_values' => $old_values,
        ];

        return $action;
    }

    /**
     * Function parse model values into array.
     * Special for Appointments, maybe something other
     * @param string $event
     * @return array
     */
    public function collectOriginDataForActionLogs(string $event = 'create'): array
    {
        $action = [];
        $action['user_id'] = auth()->id() ?? 1;

        $values = $this->getOriginal();

        $action['actions'] = [
            'event' => $event,
            'new_values' => $values,
        ];

        return $action;
    }
}
