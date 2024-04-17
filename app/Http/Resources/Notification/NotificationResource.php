<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_seen' => $this->is_seen,
            'is_showing' => $this->is_showing,
            'object' => $this->object,
            'contractor' => $this->contractor,
            'created_at' => $this->created_at
        ];
    }
}
