<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorAdditionalTypes;
use App\Models\Contractors\ContractorType;
use App\Models\Permission;
use App\Models\ProjectObject;
use App\Models\TechAcc\OurTechnic;
use App\Models\TechAcc\TechnicCategory;
use App\Models\TechAcc\TechnicMovement;
use App\Models\TechAcc\TechnicMovementStatus;
use App\Models\User;
use App\Services\Common\FileSystemService;
use App\Services\SystemService;

class TechnicMovementController extends StandardEntityResourceController
{
    public function __construct()
    { 
        parent::__construct();

        $this->baseModel = new TechnicMovement();
        $this->routeNameFixedPart = 'building::tech_acc::technic::movements::';
        $this->sectionTitle = 'Перемещения техники';
        $this->baseBladePath = resource_path() . '/views/tech_accounting/technic/technicMovements';

        // $this->isMobile =
        //     is_dir($this->baseBladePath . '/mobile')
        //     && SystemService::determineClientDeviceType($_SERVER["HTTP_USER_AGENT"]) === 'mobile';

        // $this->componentsPath =
        //     $this->isMobile
        //         ?
        //         $this->baseBladePath . '/mobile/components'
        //         : $this->baseBladePath . '/desktop/components';

        $this->componentsPath = $this->baseBladePath.'/desktop/components';

        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
        $this->modulePermissionsGroups = [13];
        $this->morphable_type = 'App\Models\TechAcc\TechnicMovement';
        $this->storage_name = 'technic_movements';

    }

    public function setResources()
    {
        $this->resources->technicCategories = $this->getTechnicCategories();
        $this->resources->technicMovementStatuses = $this->getTechnicMovementStatuses();
        $this->resources->technicsList = $this->getTechnicsList();
        $this->resources->technicResponsiblesByTypes = $this->getTechnicResponsiblesByTypes();
        $this->resources->technicResponsiblesAllTypes = $this->getTechnicResponsiblesAllTypes();
        // $this->resources->technicCarriers = $this->getTechnicCarriers();
        // $this->resources->projectObjects = $this->getProjectObjects();
    }

    public function getTechnicCategories()
    {
        return TechnicCategory::all();
    }
    
    public function getTechnicMovementStatuses()
    {
        return TechnicMovementStatus::all();
    }

    public function getTechnicsList()
    {
        return OurTechnic::all();
    }

    public function getTechnicCarriers()
    {
        return Contractor::byTypeSlug('technic_carrier');
    }

    public function getTechnicResponsiblesByTypes()
    {
        return [
            'oversize' => User::whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_oversized_equipment'))->get(),
            'standartSize' => User::whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_standart_sized_equipment'))->get()
        ];
        // return json_encode([
        //     'oversize' => User::whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_oversized_equipment'))->get(),
        //     'standartSize' => User::whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_standart_sized_equipment'))->get()
        // ],
        // JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function getTechnicResponsiblesAllTypes()
    {
        return
            User::
                whereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_oversized_equipment'))
                ->orWhereIn('id', Permission::UsersIdsByCodename('technics_processing_movement_standart_sized_equipment'))
            ->get();
    }

    public function getProjectObjects()
    {
        return ProjectObject::
            where('is_participates_in_material_accounting', 1)
            ->whereNotNull('short_name')
            ->orderBy('short_name')
            ->get();
    }
}
