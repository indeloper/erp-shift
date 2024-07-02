<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectObjectContactResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'note'                 => $this->note,
            'contact_full_name'    => $this->contact->last_name.' '
                .$this->contact->first_name.' '.$this->contact->patronymic,
            'contact_phone_number' => $this->contact->phone_number,
            'contact_position'     => $this->contact->position,
            'contact_note'         => $this->contact->note,
        ];
    }

}
