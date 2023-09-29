<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Common\FileSystemService;

class FuelTankController extends Controller
{

    public function getPageCore() 
    {
        $routeNameFixedPart = 'building::tech_acc::fuel::tanks::';
        $sectionTitle = 'Топливные ёмкости';
        $basePath = resource_path().'/views/tech_accounting/fuel/tanks/objects';
        $componentsPath = $basePath.'/desktop/components';
        $components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($componentsPath, $basePath);
        return view('tech_accounting.fuel.tanks.objects.desktop.index', compact('routeNameFixedPart', 'sectionTitle', 'basePath', 'components'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
