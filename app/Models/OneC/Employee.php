<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models\OneC;

use App\Models\User;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, DevExtremeDataSourceLoadable;

    protected $guarded = array('id');

    /**
     * @param $format
     * F - Full firstname;
     * f - Fist letter of firstName;
     * L - Full lastname;
     * l - Fist letter of lastname;
     * P - Full patronymic;
     * p - Fist letter of patronymic;
     * @param $declension
     * @return string
     */
    public function format($format = null, $declension = null): string
    {
        return User::withoutGlobalScopes()->find($this->user_id)->format($format, $declension);
    }
}