<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial\operations;

use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\Annotation\Route;

class q3wMaterialOperation extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    protected $appends = ['have_conflict', 'url', 'expected_users_names'];

    public function getHaveConflictAttribute(): bool
    {
        return in_array($this->operation_route_stage_id, [11, 19, 30, 38]);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(q3wOperationMaterial::class, 'material_operation_id', 'id');
    }

    public function routeStage(): HasOne
    {
        return $this->hasOne(q3wOperationRouteStage::class, 'id', 'operation_route_stage_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(q3wOperationComment::class, 'material_operation_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(q3wOperationFile::class, 'material_operation_id', 'id');
    }

    public function scopeOnlyActive($query)
    {
        $query->whereNotIn('operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->whereNotIn('operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'));
    }

    public function scopeOnlyCompleted($query)
    {
        $query->whereIn('operation_route_stage_id', q3wOperationRouteStage::completed()->pluck('id'))
            ->whereIn('operation_route_stage_id', q3wOperationRouteStage::cancelled()->pluck('id'));
    }



    public function scopeWithMaterialsSummary($query)
    {
        $materialRawQuery = DB::raw("(SELECT
                                              SUMMARY.id AS material_operation_id,
                                              GROUP_CONCAT(SUMMARY.material_types_info ORDER BY SUMMARY.material_types_info ASC SEPARATOR '<br>') AS material_types_info
                                            FROM (SELECT
                                                CONCAT(q3w_material_types.name, ' (', ROUND(SUM(IFNULL(amount, 1) * quantity * q3w_material_standards.weight), 3), ' т.)') AS material_types_info,
                                                q3w_material_operations.id,
                                                q3w_material_types.name
                                              FROM q3w_operation_materials
                                                LEFT JOIN q3w_material_operations
                                                  ON q3w_operation_materials.material_operation_id = q3w_material_operations.id
                                                LEFT JOIN q3w_material_standards
                                                  ON q3w_operation_materials.standard_id = q3w_material_standards.id
                                                LEFT JOIN q3w_material_types
                                                  ON q3w_material_standards.material_type = q3w_material_types.id
                                              GROUP BY q3w_material_operations.id,
                                                       q3w_material_types.name
                                              ORDER BY q3w_material_types.name) AS SUMMARY
                                            GROUP BY SUMMARY.id) AS material_summary");
        //Laravel 5 don't have addSubSelect method :( Using RAW instead
        $query->leftJoin($materialRawQuery, 'q3w_material_operations.id', '=', 'material_operation_id')
            ->addSelect('material_types_info');
    }

    public function getUrlAttribute(){
        switch ($this->operation_route_id) {
            case 1:
                $routeName = "supply";
                break;
            case 2:
                $routeName = "transfer";
                break;
            case 3:
                $routeName = "transformation";
                break;
            case 4:
                $routeName = "write-off";
                break;
            default:
                $routeName = "#";
        }

        switch ($this->routeStage->operation_route_stage_type_id) {
            case 2:
            case 7:
                $routeStageName = "completed";
                break;
            case 3:
            case 5:
            case 6:
                $routeStageName = "view";
                break;
            default:
                $routeStageName = "view";
        }
        $routeName = 'materials.operations.' . $routeName . '.' . $routeStageName;

        return route($routeName).'/?operationId=' . $this->id;
    }

    public function getExpectedUsersNamesAttribute() {
        $routeStageId = $this->operation_route_stage_id;
        switch ($routeStageId) {
            case 11:
            case 25:
                return User::find($this->source_responsible_user_id)->full_name;
            case 6:
            case 30:
                return User::find($this->destination_responsible_user_id)->full_name;
            case 37:
            case 71:
            case 77:
                $responsibilityUsers = (new ObjectResponsibleUser)
                    ->getResponsibilityUsers($this->destination_project_object_id, $role='TONGUE_PROJECT_MANAGER')
                    ->pluck('user_id');
                return User::whereIn('id', $responsibilityUsers)->get()->pluck('full_name')->join(';');
            case 19:
                $responsibilityUsers = (new ObjectResponsibleUser)
                    ->getResponsibilityUsers($this->source_project_object_id, $role='TONGUE_PROJECT_MANAGER')
                    ->pluck('user_id');
                return User::whereIn('id', $responsibilityUsers)->get()->pluck('full_name')->join(';');
            case 79:
                $permissionId = Permission::where('codename', 'material_accounting_write_off_confirmation')->first()->id;
                $userIds = UserPermission::where('permission_id', $permissionId)->pluck('user_id');
                return User::whereIn('id', $userIds)->get()->pluck('full_name')->join(';');
            default:
                return null;
        }
    }
}
