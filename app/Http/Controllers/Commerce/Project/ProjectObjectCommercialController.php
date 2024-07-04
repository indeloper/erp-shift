<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Resources\ProjectObjectCommecialResource;
use App\Models\ProjectObject;
use App\Services\ProjectObjectCommercialService;
use Illuminate\Http\Request;

final class ProjectObjectCommercialController
{

    public function __construct(
        protected ProjectObjectCommercialService $projectObjectCommercialService
    ) {}

    public function index(ProjectObject $projectObject)
    {
        return ProjectObjectCommecialResource::collection(
            $this->projectObjectCommercialService->getCommercial(
                $projectObject,
            )
        );
    }

    public function show() {}

    public function store(
        Request $request,
        ProjectObject $projectObject
    ) {
        dd($request->all());
    }

}