<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Models\TechAcc\OurTechnic;
use App\Http\Controllers\StandardEntityResourceController;
use App\Services\Common\FileSystemService;

class OurTechnicController extends StandardEntityResourceController
{

    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $basePath;
    protected $componentsPath;
    protected $components;

    public function __construct()
    {
        $this->baseModel = new OurTechnic;
        $this->routeNameFixedPart = 'building::tech_acc::technic::ourTechnicList::';
        $this->sectionTitle = 'Наша техника';
        $this->basePath = resource_path().'/views/tech_accounting/technic/ourTechnicList';
        $this->componentsPath = $this->basePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->basePath);
    }
    
}
