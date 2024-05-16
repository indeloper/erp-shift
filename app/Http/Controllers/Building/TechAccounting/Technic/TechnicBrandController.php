<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicBrand;

class TechnicBrandController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Марки техники';
        $this->baseModel = new TechnicBrand;
        $this->routeNameFixedPart = 'building::tech_acc::technic::technicBrand::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicBrand';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents();
    }
}
