<?php


namespace App\Http\ViewComposers;


use Illuminate\View\View;

class TimecardComposer
{
    public function compose(View $view)
    {
        $isUserHasWorkers = auth()->user()->timeResponsibleProjects()->where(function($rel) {
            $rel->whereHas('users')->orWhereHas('brigades');
        })->exists() and auth()->id() != 1;
        $view->with('show_daily', $isUserHasWorkers);
    }
}
