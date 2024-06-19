<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShortNameProjectObjectRequest;
use App\Http\Resources\ShortNameProjectObjectResource;
use App\Services\ShortNameProjectObjectService;

class ShortNameProjectObjectController extends Controller
{

    public function __construct(
        protected ShortNameProjectObjectService $shortNameProjectObjectService
    ) {}

    public function store(ShortNameProjectObjectRequest $request)
    {
        return ShortNameProjectObjectResource::make(
            $this->shortNameProjectObjectService->store($request)
        );
    }

}
