<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'editor_FIO' => $this->user->last_name.' '.$this->user->first_name
                .' '.$this->user->patronymic,
            ...$this->actions['new_values'],
        ];
    }

}
