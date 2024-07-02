<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce\Project;

use App\Domain\DTO\ProjectObjectContractor\ProjectObjectContractorData;
use App\Http\Requests\ProjectObjectContractorRequest;
use App\Http\Resources\ProjectObjectContractorResource;
use App\Models\ProjectObject;
use App\Models\ProjectObjectContractor;
use App\Services\Bitrix\BitrixServiceInterface;
use App\Services\ProjectObjectContractorService;

final class ProjectObjectContractosController
{

    public function __construct(
        protected ProjectObjectContractorService $projectObjectContractorService
    ) {}

    public function index(ProjectObject $projectObject)
    {
        return ProjectObjectContractorResource::collection(
            $projectObject
                ->contractors()
                ->with(['user'])
                ->paginate()
        );
    }


    public function show(int $id)
    {
        return ProjectObjectContractorResource::make(
            ProjectObjectContractor::query()->findOrFail($id)
        );
    }

    public function store(ProjectObject $projectObject, ProjectObjectContractorRequest $request)
    {
        $data = $request->collect('data');

        $projectObjectContractor = $this->projectObjectContractorService->store(
            auth()->user(),
            $projectObject,
            ProjectObjectContractorData::make([
                'contractor_id' => $data->get('contractor_id'),
                'is_main' => $data->has('is_main'),
            ])
        );

        if (isset($projectObject->bitrix_id)) {
            \app(BitrixServiceInterface::class)->updateDealByModal(
                $projectObject
            );
        }

        return ProjectObjectContractorResource::make(
            $projectObjectContractor
        );
    }

}