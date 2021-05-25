<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\Contractors\Contractor;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wOperationRoute;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\q3wMaterial\q3wProjectObjectMaterialAccountingType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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

        return (new ProjectObject)->dxLoadOptions($options)
            ->whereNotNull('short_name')
            ->get(['id', 'name', 'short_name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function ContractorsList(Request $request)
    {
        $dxLoadOptions = json_decode($request['data'])->dxLoadOptions;

        return (new Contractor)->dxLoadOptions($dxLoadOptions)
            ->get(['id', 'short_name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function usersList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new User)->dxLoadOptions($options)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('patronymic')
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function operationRoutesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wOperationRoute())->dxLoadOptions($options)->get(['id', 'name'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function operationRouteStagesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wOperationRouteStage())->dxLoadOptions($options)->get(['id', 'name'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
    public function operationRouteStagesWithoutNotificationsList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wOperationRouteStage())->dxLoadOptions($options)
            ->select(['name'])
            ->where('operation_route_stage_type_id', '<>', 4)
            ->distinct()
            ->orderBy('name')
            ->get(['name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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

    public function materialTypesLookupList(Request $request) {
        $dxLoadOptions = json_decode($request['data'])->dxLoadOptions;

        return (new q3wMaterialType())->dxLoadOptions($dxLoadOptions)
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function projectObjectMaterialAccountingTypesLookupList(Request $request) {
        $types = (new q3wProjectObjectMaterialAccountingType())->get();

        $results = [];

        foreach ($types as $type) {
            $results[] = [
                'id' => $type->id,
                'text' => $type->name,
            ];
        }

        return ['results' => $results];
    }
}
