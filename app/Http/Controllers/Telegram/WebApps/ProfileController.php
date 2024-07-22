<?php

namespace App\Http\Controllers\Telegram\WebApps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telegram\WebApps\UpdateProfileRequest;

class ProfileController extends Controller
{

    public function show()
    {
        return view('telegram.web-apps.profile');
    }

    public function update(UpdateProfileRequest $request)
    {
        dd(123);
    }

}
