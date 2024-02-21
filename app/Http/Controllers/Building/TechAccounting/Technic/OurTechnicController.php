<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use App\Http\Controllers\Common\BaseControllers\GridResourceController;
use App\Models\Company\Company;
use App\Models\Contractors\Contractor;
use App\Models\Employees\Employee;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicBrand;
use App\Models\TechAcc\TechnicBrandModel;
use App\Models\TechAcc\TechnicCategory;
use Illuminate\Http\Request;

class OurTechnicController extends GridResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->sectionTitle = 'Учет техники';
        $this->baseModel = new OurTechnic;
        $this->routeNameFixedPart = 'building::tech_acc::technic::ourTechnicList::';
        $this->baseBladePath = resource_path() . '/views/tech_accounting/technic/ourTechnicList';
        $this->isMobile = $this->isMobile($this->baseBladePath);
        $this->components = $this->getModuleComponents();
        $this->modulePermissionsGroups = [13];
        $this->ignoreDataKeys[] = 'third_party_mark_2';
    }

    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->leftJoin('technic_movements', function ($join) {
                $join->on('technic_movements.technic_id', '=', 'our_technics.id')->whereRaw("
                    technic_movements.id
                        IN (SELECT MAX(technic_movements.id)
                            FROM technic_movements
                            JOIN technic_movement_statuses ON technic_movement_statuses.id = technic_movements.technic_movement_status_id AND slug IN ('completed', 'inProgress')
                            GROUP BY technic_id)
                    ");
            })
            ->leftJoin('technic_movement_statuses', 'technic_movements.technic_movement_status_id', '=', 'technic_movement_statuses.id')
            ->select('our_technics.*', 'technic_movements.object_id', 'technic_movements.previous_object_id', 'technic_movement_statuses.slug as status_slug')
            ->get();

        return json_encode(array("data" => $entities), JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function setAdditionalResources()
    {
        $this->additionalResources->technicCategories = TechnicCategory::all();

        $this->additionalResources->technicResponsibles = Employee::query()->where('dismissal_date', '0000-00-00')->orWhereNull('dismissal_date')->leftJoin('users', 'users.id', '=', 'employees.user_id')->select(['employees.id', 'users.user_full_name'])->orderBy('last_name')->get();

        $this->additionalResources->technicBrands = TechnicBrand::all();

        $this->additionalResources->technicModels = TechnicBrandModel::all();

        $this->additionalResources->companies = Company::all();

        $this->additionalResources->contractors = Contractor::byTypeSlug('technic_lessor');

        $this->additionalResources->objects = ProjectObject::whereNotNull('short_name')->get();
    }
}
