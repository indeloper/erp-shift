<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        dd($this['projectObjects'][0]);
//        dd($this['projectObjects']->map(function ($item) {
//            return ['id' => $item->id, 'name' => $item->name, 'short_name' => $item->short_name];
//        }));
//        dd([
//            'user_id' => $this['user_id'],
//            'accounting_types' => $this['accountingTypes']->map(function ($item) {
//                return ['value' => $item->value];
//            })
//        ]);
        return [
            'user_id' => $this['user_id'],
            'measure_units' => $this['measureUnits']->map(function ($item) {
                return [$item->id => $item->value];
            }),
            'accounting_types' => $this['accountingTypes']->map(function ($item) {
                return [$item->id => $item->value];
            }),
            'material_types' => $this['materialTypes']->map(function ($item) {
                return [$item->id => $item->name];
            }),
            'material_standards' => $this['materialStandards'],
            'project_objects' => $this['projectObjects']->map(function ($item) {
                return ['id' => $item->id, 'name' => $item->name, 'short_name' => $item->short_name];
            }),
            'users' => $this['users'],
            'materials_actual_list_route' => $this['materialsActualListRoute'],
            'materials_standards_listex_route' => $this['materialsStandardsListexRoute'],
            'material_accounting_list_route' => $this['materialAccountingListRoute'],
            'material_transform_types_lookup_list_route' => $this['materialTransformTypesLookupListRoute'],
            'users_with_material_list_access_list_route' => $this['usersWithMaterialListAccessListRoute']
        ];
    }
}
