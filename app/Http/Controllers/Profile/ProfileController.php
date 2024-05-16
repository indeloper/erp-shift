<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserServiceInterface;

class ProfileController extends Controller
{
    /** @var UserServiceInterface */
    public $userService;

    public function __construct(
        UserServiceInterface $userService
    ) {
        $this->userService = $userService;
    }

    public function show()
    {
        return UserResource::make(
            $this->userService->getUserById(
                auth()->user()->id
            )
        );
    }
}
