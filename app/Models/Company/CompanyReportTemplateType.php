<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\Company;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyReportTemplateType extends Model
{
    use DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];
}
