<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\Timesheet;

use App\Traits\AuthorAndEditorUserFields;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeesReportGroup extends Model
{
    use AuthorAndEditorUserFields, DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];
}
