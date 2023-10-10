<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Models\TechAcc\OurTechnic;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\TechAcc\TechnicBrand;
use App\Models\TechAcc\TechnicBrandModel;
use App\Models\TechAcc\TechnicCategory;
use App\Models\User;
use App\Services\Common\FileSystemService;

class OurTechnicController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new OurTechnic;
        $this->routeNameFixedPart = 'building::tech_acc::technic::ourTechnicList::';
        $this->sectionTitle = 'Учёт техники';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/ourTechnicList';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }

    public function getTechnicResponsibles()
    {
        return User::query()->active()
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
    }

    public function getTechnicBrands()
    {
        return TechnicBrand::all();
    }

    public function getTechnicModels()
    {
        return TechnicBrandModel::all();
    }
    
}
