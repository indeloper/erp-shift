<?php

namespace App\Models\Timesheet;

use App\Traits\AuthorAndEditorUserFields;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees1cSalary extends Model
{
    use AuthorAndEditorUserFields, SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    protected $table = 'employees_1c_salaries';
}