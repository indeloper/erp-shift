<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial\operations;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class q3wMaterialOperation extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    public function materials()
    {
        return $this->hasMany(q3wOperationMaterial::class, 'material_operation_id', 'id');
    }

    public function scopeOnlyActive($query)
    {
        $query->whereNotIn('operation_route_stage_id', [3, 11, 12]);
    }

    public function scopeOnlyCompleted($query)
    {
        $query->whereIn('operation_route_stage_id', [3, 11, 12]);
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
        //Laravel 5 dont have addSubSelect method :( Using RAW instead
        $query->leftJoin($materialRawQuery, 'q3w_material_operations.id', '=', 'material_operation_id')
            ->addSelect('material_types_info');

        /*['last_login_at' => Login::select('created_at')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->take(1)
        ]*/
    }
}
