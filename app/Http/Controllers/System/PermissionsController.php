<?php

namespace App\Http\Controllers\System;

use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PermissionsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = json_decode($request['data']);
        $permissions =
            (new Permission)
            ->dxLoadOptions($options, true)
            ->get();

        return json_encode(array(
                "data" => $permissions,
                "totalCount" => $permissions->count()
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
        try {
            $permission = new Permission(json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY /*| JSON_THROW_ON_ERROR)*/));
            $permission->save();

            return response()->json([
                'result' => 'ok',
                'key' => $permission->id
            ], 200);

        } catch(Exception $e) {
            return response()->json([
                'result' => 'error',
                'errors'  => $e->getMessage(),
            ], 400);
        }
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
        $req =  json_decode($request["data"]);
        $permission = Permission::findOrFail($id);

        $permission->name = $req->name ?? $permission->name;
        $permission->codename = $req->codename ?? $permission->name;
        $permission->category = $req->category ?? $permission->category;

        $permission->save();

        return response()->json([
            'result' => 'ok',
            'request' => $request
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);
        $permission->delete();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    function getCategories() {
        $categories = (new Permission)->categories;
        $categoriesArray = [];
        foreach($categories as $key=>$value){
            $categoryObject = new \stdClass;
            $categoryObject->id = $key;
            $categoryObject->value = $value;
            $categoriesArray[] = $categoryObject;
        }
        return response()->json($categoriesArray, 200);
    }
}