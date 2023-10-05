<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Services\Common\FileSystemService;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicBrandModel;

class TechnicBrandModelController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new TechnicBrandModel;
        $this->routeNameFixedPart = 'building::tech_acc::technic::technicBrandModel::';
        $this->sectionTitle = 'Модели техники';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/technicBrandModel';
        
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }
}
