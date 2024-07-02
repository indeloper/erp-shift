<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Requests\ProjectObjectContactRequest;
use App\Http\Resources\ProjectObjectContactResource;
use App\Models\ProjectObject;
use App\Models\ProjectObjectContact;
use App\Services\ProjectObjectContactService;

final class ProjectObjectContactsController
{

    public function __construct(
        protected ProjectObjectContactService $projectObjectContactService
    ) {}

    public function index(ProjectObject $projectObject)
    {
        return ProjectObjectContactResource::collection(
            ProjectObjectContact::query()
                ->where('project_object_id', $projectObject->id)
                ->paginate()
        );
    }

    public function show() {}

    public function store(
        ProjectObjectContactRequest $request,
        ProjectObject $projectObject
    ) {
        $data = $request->collect('data');

        return ProjectObjectContactResource::make(
            $this->projectObjectContactService->store($projectObject,
                (int) $data->get('contact_id'),
                (string) $data->get('note', '')
            )
        );
    }

}