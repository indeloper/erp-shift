<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Resources\ProjectObjectWorkVolumeResource;
use App\Models\ProjectObject;
use App\Services\ProjectObjectWorkVolumesService;
use Illuminate\Http\Request;

final class ProjectObjectWorkVolumesController
{

    public function __construct(
        protected ProjectObjectWorkVolumesService $projectObjectWorkVolumesService
    ) {}

    public function index(ProjectObject $projectObject)
    {
        return ProjectObjectWorkVolumeResource::collection(
            $this->projectObjectWorkVolumesService->getWorkValues(
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