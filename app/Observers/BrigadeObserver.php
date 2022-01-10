<?php

namespace App\Observers;

use App\Models\HumanResources\Brigade;

class BrigadeObserver
{
    /**
     * Handle the brigade "created" event.
     *
     * @param  Brigade  $brigade
     * @return void
     */
    public function created(Brigade $brigade)
    {
        $brigade->generateAction();
        $brigade->generateBrigadeCreateNotifications();
    }

    /**
     * Handle the brigade "updated" event.
     *
     * @param  Brigade  $brigade
     * @return void
     */
    public function updated(Brigade $brigade)
    {
        if (! in_array('deleted_at', array_keys($brigade->getChanges()))) {
            $brigade->generateAction('update');
            $brigade->generateBrigadeUpdateNotifications();
        }
    }

    /**
     * Handle the brigade "deleted" event.
     *
     * @param  Brigade  $brigade
     * @return void
     */
    public function deleted(Brigade $brigade)
    {
        $brigade->users->each(function ($user) {
            $user->update(['brigade_id' => null]);
        });
        $brigade->generateAction('destroy');
        $brigade->generateBrigadeDestroyNotifications();
        $brigade->update(['foreman_id' => null]);

    }

    /**
     * Handle the brigade "restored" event.
     *
     * @param  Brigade  $brigade
     * @return void
     */
    public function restored(Brigade $brigade)
    {
        //
    }

    /**
     * Handle the brigade "force deleted" event.
     *
     * @param  Brigade  $brigade
     * @return void
     */
    public function forceDeleted(Brigade $brigade)
    {
        //
    }
}
