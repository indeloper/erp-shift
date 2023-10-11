<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Company\Company;
use App\Models\Group;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankFlow;
use App\Models\TechAcc\FuelTank\FuelTankMovement;
use App\Models\User;
use App\Services\Common\FileSystemService;
use Illuminate\Support\Facades\Auth;

class FuelTankController extends StandardEntityResourceController
{
    public function __construct()
    {
        $this->baseModel = new FuelTank;
        $this->routeNameFixedPart = 'building::tech_acc::fuel::tanks::';
        $this->sectionTitle = 'Топливные ёмкости';
        $this->baseBladePath = resource_path().'/views/tech_accounting/fuel/tanks/objects';
        $this->componentsPath = $this->baseBladePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
    }
    
    public function afterStore($tank, $data)
    {
        FuelTankMovement::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $tank->id,
            'object_id' => $tank->object_id,
            'fuel_level' => 0
        ]);
    }

    public function beforeUpdate($entity, $data)
    {
        FuelTankMovement::create([
            'author_id' => Auth::user()->id,
            'fuel_tank_id' => $entity->id,
            'previous_object_id' => $entity->object_id,
            'object_id' => $data['object_id'] ?? $entity->object_id ?? null,
            'fuel_level' => $entity->fuel_level
        ]);

        return [
            'data' => $data, 
            'ignoreDataKeys' => []
        ];
    }

    public function getFuelTanksResponsibles()
    {
        return User::query()->active()
                ->whereIn('group_id', Group::FOREMEN)
                ->select(['id', 'user_full_name'])
                ->orderBy('last_name')
                ->get();
    }

    public function getCompanies() {
        $companies = Company::all();
        return response()->json($companies, 200, [], JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK);
    }

    public function getProjectObjects(Request $request)
    {
        $options = json_decode($request['data']);
        
        $objects = (new ProjectObject)
            ->where('is_participates_in_material_accounting', 1)
            ->whereNotNull('short_name')
            ->orderBy('short_name')
            ->get()
            ->toArray();

        return json_encode(
            $objects
        ,
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
