<?php
/**  * @mixin ..\Eloquent */

namespace App\Models\Employees;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees1cPost extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $table = 'employees_1c_posts';
    protected $guarded = array('id');

    function getInflection($inflection)
    {
        switch (mb_strtolower($inflection)) {
            case "genitive":
            case "родительный":
                $inflectionFieldName = "genitive";
                break;
            case "dative":
            case "дательный":
                $inflectionFieldName = "dative";
                break;
            case "accusative":
            case "винительный":
                $inflectionFieldName = "accusative";
                break;
            case "ablative":
            case "творительный":
                $inflectionFieldName = "ablative";
                break;
            case "prepositional":
            case "предложный":
                $inflectionFieldName = "prepositional";
                break;
            default:
                $inflectionFieldName = "nominative";
        }

        $inflectionQuery = Employees1cPostInflection::where('post_id', '=', $this->id)
            ->addSelect($inflectionFieldName . ' as inflection')
            ->first();

        if (isset($inflectionQuery)) {
            return $inflectionQuery->inflection;
        } else {
            return $this->name;
        }
    }
}
