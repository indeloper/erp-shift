<?php

namespace App\Observers;

use App\Models\ShortNameProjectObject;

class ShortNameProjectObjectObserver
{

    /**
     * Handle the ShortNameProjectObject "created" event.
     */
    public function created(ShortNameProjectObject $shortNameProjectObject
    ): void {
        //
    }

    /**
     * Handle the ShortNameProjectObject "updated" event.
     */
    public function updated(ShortNameProjectObject $shortNameProjectObject
    ): void {
        $shortNameProjectObject->generateAction('update');
    }

    /**
     * Handle the ShortNameProjectObject "deleted" event.
     */
    public function deleted(ShortNameProjectObject $shortNameProjectObject
    ): void {
        //
    }

    /**
     * Handle the ShortNameProjectObject "restored" event.
     */
    public function restored(ShortNameProjectObject $shortNameProjectObject
    ): void {
        //
    }

    /**
     * Handle the ShortNameProjectObject "force deleted" event.
     */
    public function forceDeleted(ShortNameProjectObject $shortNameProjectObject
    ): void {
        //
    }

}
