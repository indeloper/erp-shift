<?php

namespace App\Models\Timesheet;

use App\Traits\AuthorAndEditorUserFields;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimesheetCard extends Model
{
    use AuthorAndEditorUserFields, SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');
}