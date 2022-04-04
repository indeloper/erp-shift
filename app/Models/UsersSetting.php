<?php
/**  * @mixin ..\Eloquent  */
namespace App\Models;

use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UsersSetting extends Model
{
    use SoftDeletes;

    public function setSetting($codename, $value){
        $this->updateOrInsert(
            ['codename' => $codename, 'user_id' => Auth::id()],
            ['value' => $value]
        );
    }

    public function getSetting($codename){
        return $this->where('codename', 'like', $codename)
            ->where('user_id', '=', Auth::id())
            ->first()
            ->value;
    }
}
