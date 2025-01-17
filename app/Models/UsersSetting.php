<?php

/**  * @mixin ..\Eloquent  */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UsersSetting extends Model
{
    use SoftDeletes;

    public function setSetting($codename, $value)
    {
        $this->updateOrInsert(
            ['codename' => $codename, 'user_id' => Auth::id()],
            ['value' => $value]
        );
    }

    public function getSetting($codename)
    {
        $setting = $this->where('codename', 'like', $codename)
            ->where('user_id', '=', Auth::id())
            ->first();

        if (isset($setting)) {
            return $setting->value;
        } else {
            return null;
        }
    }
}
