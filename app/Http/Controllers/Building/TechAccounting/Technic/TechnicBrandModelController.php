<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicBrand;
use App\Models\TechAcc\TechnicBrandModel;

class TechnicBrandModelController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Модели техники';
        $this->baseModel = new TechnicBrandModel;
        $this->routeNameFixedPart = 'building::tech_acc::technic::technicBrandModel::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicBrandModel';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents(); 
    }

    public function setAdditionalResources()
    {
        $this->additionalResources->
        technicBrands = TechnicBrand::all();
    }
}
