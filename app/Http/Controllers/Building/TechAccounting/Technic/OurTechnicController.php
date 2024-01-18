<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use App\Models\TechAcc\OurTechnic;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Company\Company;
use App\Models\Contractors\Contractor;
use App\Models\Employees\Employee;
use App\Models\TechAcc\TechnicBrand;
use App\Models\TechAcc\TechnicBrandModel;
use App\Models\TechAcc\TechnicCategory;

class OurTechnicController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Учет техники';
        $this->baseModel = new OurTechnic;
        $this->routeNameFixedPart = 'building::tech_acc::technic::ourTechnicList::';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/ourTechnicList';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents(); 
        $this->modulePermissionsGroups = [13];
    }
    
    public function setAdditionalResources()
    {
        $this->additionalResources->
        technicCategories = TechnicCategory::all();

        $this->additionalResources->
        technicResponsibles =
            Employee::query()
            ->where('dismissal_date', '0000-00-00')
            ->orWhereNull('dismissal_date')
            ->leftJoin('users', 'users.id', '=', 'employees.user_id')
            ->select(['employees.id', 'users.user_full_name'])
            ->orderBy('last_name')
            ->get();
        
        $this->additionalResources->
        technicBrands = TechnicBrand::all();

        $this->additionalResources->
        technicModels = TechnicBrandModel::all();

        $this->additionalResources->
        companies = Company::all();

        $this->additionalResources->
        contractors = Contractor::byTypeSlug('technic_lessor');
    }
}
