<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Resources\ProjectObjectEventResource;
use App\Models\ProjectObject;
use App\Services\ProjectObjectEventService;
use Illuminate\Http\Request;

final class ProjectObjectEventsController
{

    public function __construct(
        protected ProjectObjectEventService $projectObjectEventService
    ) {}

    public function index(ProjectObject $projectObject)
    {
        return ProjectObjectEventResource::collection(
            $this->projectObjectEventService->getEvents(
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