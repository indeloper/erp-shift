<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Http\Resources\LogResource;
use App\Http\Resources\ProjectObjectResource;
use App\Models\ProjectObject;
use App\Repositories\ProjectObject\ProjectObjectRepository;
use Illuminate\Http\Request;

final class ProjectObjectController
{

    public function __construct(
        public ProjectObjectRepository $projectObjectRepository
    ) {}

    public function index(Request $request)
    {
        return ProjectObject::query()
            ->where('project_id', $request->get('project_id'))
            ->paginate(15);
    }

    public function show($id)
    {
        $object = $this->projectObjectRepository->getById((int) $id);

        return ProjectObjectResource::make(
            $object
        );
    }

    public function historyChanges(
        ProjectObject $projectObject,
    ) {
        return LogResource::collection(
            $projectObject->shortName?->logs()?->with(['user'])?->paginate(15)
            ?? []
        );
    }

}