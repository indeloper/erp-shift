<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $route = null;

        if ($this->route_name) {
            $route = route($this->route_name);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'route' => $route,
            'icon_path' => $this->icon_path,
            'actives' => $this->actives,
            'children' => self::collection($this->children),
            'toggle_favorite_route' => route('layout::menu::favorite::toggle', [
                'menu_item' => $this->id
            ]),
            'is_favorite' => auth()->user()->menuItems->contains($this->id)
        ];
    }
}