<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectObjectEventResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'name'             => $this->name,
            'user_full_name'   => $this->responsible_user?->long_full_name ??
                'Система',
            'author_full_name' => $this->user?->full_name ?? 'Система',
        ];
    }

}
