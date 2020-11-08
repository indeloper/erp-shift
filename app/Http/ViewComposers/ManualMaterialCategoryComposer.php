<?php

namespace App\Http\ViewComposers;

use App\Models\Notification;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Manual\ManualMaterialCategory;


class ManualMaterialCategoryComposer
{
    public function __construct()
    {

    }

    public function compose(View $view)
    {
        $categories = ManualMaterialCategory::whereNotIn('id', [12,14])->with('attributes')->select('id', 'name')->get();

        $view->with('categories', $categories);
    }
}
