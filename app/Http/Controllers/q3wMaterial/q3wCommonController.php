<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wOperationRoute;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class q3wCommonController extends Controller
{
    /**
     * @param Request $request
     * @return string
     */
    public function projectObjectsList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new ProjectObject)->dxLoadOptions($options)->get(['id', 'name', 'short_name'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function operationRoutesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wOperationRoute())->dxLoadOptions($options)->get(['id', 'name'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function measureUnitsList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wMeasureUnit())->dxLoadOptions($options)->get(['id', 'value'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function materialAccountingTypesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wMaterialAccountingType())->dxLoadOptions($options)->get(['id', 'value'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}