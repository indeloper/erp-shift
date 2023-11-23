<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Services\Common\FileSystemService;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicBrand;

class TechnicBrandController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new TechnicBrand;
        $this->routeNameFixedPart = 'building::tech_acc::technic::technicBrand::';
        $this->sectionTitle = 'Марки техники';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicBrand';

        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }

    public function getTechnicBrands()
    {
        return TechnicBrand::all();
    }
}
