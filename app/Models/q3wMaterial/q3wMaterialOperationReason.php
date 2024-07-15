<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\q3wMaterial;

use App\Traits\AuthorAndEditorUserFields;
use App\Traits\DefaultSortable;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class q3wMaterialOperationReason extends Model
{
    use SoftDeletes, AuthorAndEditorUserFields, DefaultSortable, DevExtremeDataSourceLoadable;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'name' => 'asc'
    ];
}
