<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $data = json_decode($request->input('data'));
        $data->author_id = Auth::user()->id;
        $this->baseModel->create((array)$data);
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
        $this->baseModel::find($id)->update((array)json_decode($request->input('data')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->baseModel::find($id)->delete();
    }
}
