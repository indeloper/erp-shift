<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StandardEntityResourceController extends Controller
{
    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $basePath;
    protected $componentsPath;
    protected $components;

    public function getPageCore() 
    {
        return view('tech_accounting.fuel.tanks.objects.desktop.index',
        [
            'routeNameFixedPart' => $this->routeNameFixedPart,
            'sectionTitle' => $this->sectionTitle, 
            'basePath' => $this->basePath, 
            'components' => $this->components
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = json_decode($request['data']);

        $entities = $this->baseModel
            ->dxLoadOptions($options)
            ->orderBy('id', 'desc')
            ->get();

        return json_encode(array(
            "data" => $entities
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = (array)json_decode($request->input('data'));
        
        DB::beginTransaction();
            $data = $this->beforeStore($data);
            $entity = $this->baseModel->create($data);
            $this->afterStore($entity, $data);
        DB::commit();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->baseModel::find($id);
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
        $data = (array)json_decode($request->input('data'));
        
        DB::beginTransaction();
            $data = $this->beforeUpdate($id, $data);
            $this->baseModel::findOrFail($id)->update($data);
            $this->afterUpdate($id, $data);
        DB::commit();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $entity = $this->baseModel::findOrFail($id);
        DB::beginTransaction();
            $this->beforeDelete($entity);
            $entity->delete();
            $this->afterDelete($entity);
        DB::commit();
    }

    public function beforeStore($data)
    {
        return $data;
    }

    public function afterStore($entity, $data)
    {
        return $data;
    }

    public function beforeUpdate($id, $data)
    {
        return $data;
    }

    public function afterUpdate($id, $data)
    {
        return $data;
    }

    public function beforeDelete($entity)
    {
       //
    }

    public function afterDelete($entity)
    {
       //
    }
}
