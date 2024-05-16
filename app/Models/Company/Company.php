<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models\Company;

use App\Traits\DefaultSortable;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use DefaultSortable, DevExtremeDataSourceLoadable, SoftDeletes;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'legal_form_id' => 'asc',
        'id' => 'asc',
    ];
}
