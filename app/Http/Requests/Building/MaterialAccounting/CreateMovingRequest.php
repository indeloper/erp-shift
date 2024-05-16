<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateMovingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userCanCreateOnlyDrafts = boolval(! auth()->user()->hasPermission('mat_acc_moving_create') and auth()->user()->hasPermission('mat_acc_moving_draft_create') and $this->responsible_RP != 'old_operation');

        // can create operation with back date
        if (auth()->user()->hasPermission('mat_acc_moving_create')) {
            $afterThisDate = Carbon::now()->subDays(60)->format('d.m.Y');
        } else {
            $afterThisDate = Carbon::today()->format('d.m.Y');
        }

        return [
            'from_responsible_user' => 'required|exists:users,id',
            'to_responsible_user' => 'required|exists:users,id',
            'object_id_from' => 'required|different:object_id_to|exists:project_objects,id',
            'object_id_to' => 'required|exists:project_objects,id',
            'contract_id' => 'nullable', // required_unless:object_id,76,192

            'planned_date_from' => 'required|after_or_equal:'.$afterThisDate,
            'planned_date_to' => 'required|after_or_equal:planned_date_from',

            // 'comment' => 'required|string|max:250',

            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:manual_materials,id',
            'materials.*.material_unit' => 'required',
            'materials.*.material_count' => 'required',
            'responsible_RP' => [$userCanCreateOnlyDrafts ? 'required' : 'nullable'],
        ];
    }
}
