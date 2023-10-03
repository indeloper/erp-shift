<?php

namespace App\Http\Controllers;

use App\Services\Common\FileSystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StandardEntityResourceController extends Controller
{
    protected $baseModel;
    protected $routeNameFixedPart;
    protected $sectionTitle;
    protected $baseBladePath;
    protected $componentsPath;
    protected $components;

    
    public function getPageCore() 
    {
        return view('tech_accounting.fuel.tanks.objects.desktop.index',
        [
            'routeNameFixedPart' => $this->routeNameFixedPart,
            'sectionTitle' => $this->sectionTitle, 
            'baseBladePath' => $this->baseBladePath, 
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

        return json_encode(array(
            "stored" => $entity
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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
        $entity = $this->baseModel::findOrFail($id);
        DB::beginTransaction();
            $data = $this->beforeUpdate($entity, $data);
            $entity->update($data);
            $this->afterUpdate($entity, $data);
        DB::commit();

        return json_encode(array(
            "updated" => $entity
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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

        return json_encode(array(
            "deleted" => 'ok'
        ),
        JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function beforeStore($data)
    {
        return $data;
    }

    public function afterStore($entity, $data)
    {
        return $data;
    }

    public function beforeUpdate($entity, $data)
    {
        return $data;
    }

    public function afterUpdate($entity, $data)
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
