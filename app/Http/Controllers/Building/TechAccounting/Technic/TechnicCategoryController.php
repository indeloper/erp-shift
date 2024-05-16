<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicCategory;

class TechnicCategoryController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Категории техники';
        $this->baseModel = new TechnicCategory;
        $this->routeNameFixedPart = 'building::tech_acc::technic::technicCategory::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicCategory';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents();
    }
}
