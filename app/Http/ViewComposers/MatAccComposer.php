<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class MatAccComposer
{
    protected $users;

    public function __construct()
    {
        $this->max = 1;

        //        dd(request()->route());
    }

    public function compose(View $view)
    {
        $excludedViews = ['building.material_accounting.modules.breadcrump', 'building.material_accounting.modules.operation_title'];

        $curr = $view->operation;
        //Check if current view is not in excludedViews array
        if (! in_array($view->getName(), $excludedViews) and $view->operation) {
            if ($view->operation->parent()->count() != 0) {
                $history = $view->operation->replicate();

                $history->id = $view->operation->id;
                $history->setRelations([]);
                $history->status = 3;
                $history->is_closed = 1;

                $history->materials = $history->getParentMats();
                $history->materialsPart = $history->getParentMatParts();

                $history->materialsPart = $history->materialsPart->sortBy('created_at');
                $history->materialsPartFrom = $history->materialsPart->where('type', 8);
                $history->materialsPartTo = $history->materialsPart->where('type', 9);

                $view->operation = $history;
            }
        }
        $view->with('curr', $curr);

    }
}
