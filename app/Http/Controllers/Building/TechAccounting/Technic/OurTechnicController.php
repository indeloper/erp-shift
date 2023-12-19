<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Models\TechAcc\OurTechnic;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Company\Company;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorAdditionalTypes;
use App\Models\Contractors\ContractorType;
use App\Models\Employees\Employee;
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
        $this->sectionTitle = 'Учет техники';
        $this->baseBladePath = resource_path().'/views/tech_accounting/technic/ourTechnicList';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
        $this->modulePermissionsGroups = [13];
    }

    public function getTechnicResponsibles()
    {
        return Employee::query()
            ->where('dismissal_date', '0000-00-00')
            ->orWhereNull('dismissal_date')
            ->leftJoin('users', 'users.id', '=', 'employees.user_id')
            ->select(['employees.id', 'users.user_full_name'])
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

    public function getCompanies()
    {
        return Company::all();
    }

    public function getContractors()
    {
        return Contractor::query() 
        ->where(
            'main_type', ContractorType::where('slug', 'technic_lessor')
            ->first()->id
            )
        ->orWhereIn('id', ContractorAdditionalTypes::where(
            'additional_type', ContractorType::where(
                'slug', 'technic_lessor'
                )
            ->first()->id)->pluck('contractor_id')->toArray()
        )
        ->get();
    }
    
}
