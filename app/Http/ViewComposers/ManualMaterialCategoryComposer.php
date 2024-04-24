<?php

namespace App\Http\ViewComposers;

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\View\View;

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
