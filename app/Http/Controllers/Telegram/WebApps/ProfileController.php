<?php

namespace App\Http\Controllers\Telegram\WebApps;

use App\Domain\DTO\User\UpdateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Telegram\WebApps\UpdateProfileRequest;
use App\Services\User\UserService;

class ProfileController extends Controller
{

    public function __construct(
        protected UserService $userService
    ) {}

    public function show()
    {
        $user = request()->user();

        return view('telegram.web-apps.profile', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->userService->updateUser(
            $request->user(),
            new UpdateUserData(
                $request->get('email'),
                $request->get('INN'),
                $request->get('first_name'),
                $request->get('last_name'),
                $request->get('patronymic'),
                $request->get('birthday'),
                $request->get('person_phone'),
                $request->get('work_phone'),
            )
        );

        session()?->flash('success', __('Данные сохранены!'));

        return back();
    }

}
