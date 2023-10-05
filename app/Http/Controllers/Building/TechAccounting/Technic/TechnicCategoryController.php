<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Common\FileSystemService;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicCategory;

class TechnicCategoryController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new TechnicCategory;
        $this->routeNameFixedPart = 'building::tech_acc::technic::technicCategory::';
        $this->sectionTitle = 'Категории техники';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicCategory';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }

    public function getTechnicCategories()
    {
        return TechnicCategory::all();
    }
}
