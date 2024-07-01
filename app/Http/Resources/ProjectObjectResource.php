<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectObjectResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cadastral_number'                       => $this->cadastral_number,
            'material_accounting_type'               => $this->material_accounting_type,
            'responsibles_pto'                       => $this->responsibles_pto,
            'responsibles_managers'                  => $this->responsibles_managers,
            'responsibles_foremen'                   => $this->responsibles_foremen,
            'id'                                     => $this->id,
            'bitrix_id'                              => $this->bitrix_id,
            'is_participates_in_documents_flow'      => $this->is_participates_in_documents_flow,
            'is_participates_in_material_accounting' => $this->is_participates_in_material_accounting,
            'name'                                   => $this->name,
            'direction'                              => $this->direction,
            'address'                                => $this->address,
            'short_name'                             => $this->short_name,
        ];
    }

}
