<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Illuminate\Foundation\Http\FormRequest;

use \Carbon\Carbon;

class CreateArrivalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $return = true;
        foreach ($this->materials as $material) {
            if ($material['used'] ?? false) {
                $return = boolval(auth()->user()->hasPermission('mat_acc_base_move_to_used'));
            }
            if ($return === false) {
                break;
            }
        }
        return $return;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userCanCreateOnlyDrafts = boolval(! auth()->user()->hasPermission('mat_acc_arrival_create') and auth()->user()->hasPermission('mat_acc_arrival_draft_create') and $this->responsible_RP != 'old_operation');

        // can create operation with back date
        if (auth()->user()->hasPermission('mat_acc_arrival_create')) {
            $afterThisDate = Carbon::today()->subDays(60)->format('d.m.Y');
        } else {
            $afterThisDate = Carbon::today()->format('d.m.Y');
        }

        return [
            'responsible_user_id' => 'required|exists:users,id',
            'supplier_id' => 'required|exists:contractors,id',
            'object_id' => 'required|exists:project_objects,id',
            'contract_id' => 'nullable', // required_unless:object_id,76,192

            'planned_date_from' => 'required|after_or_equal:' . $afterThisDate,
            'planned_date_to' => 'required|' . ($this->without_confirm ? '' : 'after_or_equal:planned_date_from'),

            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:manual_materials,id',
            'materials.*.material_unit' => 'required',
            'materials.*.material_count' => 'required',
            'responsible_RP' => [$userCanCreateOnlyDrafts ? 'required' : 'nullable'],
        ];
    }
}
