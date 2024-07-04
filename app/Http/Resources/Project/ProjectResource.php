<?php

namespace App\Http\Resources\Project;

use App\Models\MatAcc\MaterialAccountingOperation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'address'         => $this->address,
            'name'            => $this->name,
            'contractor_name' => $this->contractor_name,
            'tongue_statuses' => $this->tongue_statuses,
            'pile_statuses'   => $this->pile_statuses,
            'entity'          => MaterialAccountingOperation::$entities[$this->entity],
        ];
    }

}
