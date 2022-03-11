<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\Contractors\Contractor;
use App\Models\Permission;
use App\Models\ProjectObject;
use App\Models\q3wMaterial\operations\q3wMaterialOperation;
use App\Models\q3wMaterial\operations\q3wOperationRoute;
use App\Models\q3wMaterial\operations\q3wOperationRouteStage;
use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialTransformationType;
use App\Models\q3wMaterial\q3wMaterialType;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\q3wMaterial\q3wProjectObjectMaterialAccountingType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
            ->orderBy('short_name')
            ->get(['id', 'name', 'short_name', 'material_accounting_type'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function projectObjectsListWhichParticipatesInMaterialAccounting(Request $request)
    {
        $options = json_decode($request['data']);

        return (new ProjectObject)->dxLoadOptions($options)
            ->whereNotNull('short_name')
            ->where('is_participates_in_material_accounting', '=', 1)
            ->orderBy('short_name')
            ->get(['id', 'name', 'short_name', 'material_accounting_type'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function ContractorsList(Request $request)
    {
        $dxLoadOptions = json_decode($request['data'])->dxLoadOptions;

        return (new Contractor)->dxLoadOptions($dxLoadOptions)
            ->orderBy('short_name')
            ->get(['id', 'short_name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function usersList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new User)->dxLoadOptions($options)
            ->active()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('patronymic')
            ->get()
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function usersWithMaterialListAccess(Request $request)
    {
        $options = json_decode($request['data']);

        $permissionId = Permission::where('codename', 'material_accounting_material_list_access')->get()->first()->id;

        return (new User)->dxLoadOptions($options)
            ->active()
            ->leftJoin('user_permissions', function ($join) use ($permissionId) {
                $join->on('users.id', 'user_permissions.user_id');
                $join->on('user_permissions.permission_id', '=', DB::raw($permissionId));
            })
            ->leftJoin('group_permissions', function ($join) use ($permissionId) {
                $join->on('users.group_id', 'group_permissions.group_id');
                $join->on('group_permissions.permission_id', '=', DB::raw($permissionId));
            })
            ->where(function ($query) {
                $query->orWhereNotNull('user_permissions.permission_id');
                $query->orWhereNotNull('group_permissions.permission_id');
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('patronymic')
            ->distinct()
            ->get('users.*')
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

    public function materialTransformationTypesLookupList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wMaterialTransformationType())->dxLoadOptions($options)->get(['id', 'value'])->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
