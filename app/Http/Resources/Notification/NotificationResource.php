<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_seen' => $this->is_seen,
            'is_showing' => $this->is_showing,
            'object' => $this->object,
            'contractor' => $this->contractor,
            'created_at' => $this->created_at,
            'route_delete' => route('notifications::delete'),
            'route_view' => route('notifications::view'),
        ];
    }
}
