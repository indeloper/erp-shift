<?php

namespace App\Listeners\Bitrix\Task;

use App\Events\Bitrix\Task\TaskUpdateEvent;

class TaskUpdateListener
{

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(TaskUpdateEvent $event): void
    {
        dd($event);
    }

}
