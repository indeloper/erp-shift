<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StandardEntityResourceController;
use App\Models\ProjectObject;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Services\Common\FileSystemService;

class FuelTankController extends StandardEntityResourceController
{
    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $basePath;
    protected $componentsPath;
    protected $components;

    public function __construct()
    {
        $this->baseModel = new FuelTank;
        $this->routeNameFixedPart = 'building::tech_acc::fuel::tanks::';
        $this->sectionTitle = 'Топливные ёмкости';
        $this->basePath = resource_path().'/views/tech_accounting/fuel/tanks/objects';
        $this->componentsPath = $this->basePath.'/desktop/components';
        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->basePath);
    }

    public function getProjectObjects(Request $request)
    {
        $options = json_decode($request['data']);
        
        $objects = (new ProjectObject)
            ->where('is_participates_in_material_accounting', 1)
            ->orderBy('short_name')
            ->get()
            ->toArray();

        return json_encode(
            $objects
        ,
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
