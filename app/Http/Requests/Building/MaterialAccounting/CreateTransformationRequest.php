<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Illuminate\Foundation\Http\FormRequest;

use \Carbon\Carbon;

class CreateTransformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $return = true;
        foreach ($this->materials_to as $material) {
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
        $userCanCreateOnlyDrafts = boolval(! auth()->user()->hasPermission('mat_acc_transformation_create') and auth()->user()->hasPermission('mat_acc_transformation_draft_create') and $this->responsible_RP != 'old_operation');

        // can create operation with back date
        if (auth()->user()->hasPermission('mat_acc_transformation_create')) {
            $afterThisDate = Carbon::now()->subDays(60)->format('d.m.Y');
        } else {
            $afterThisDate = Carbon::today()->format('d.m.Y');
        }

        return [
            'responsible_user_id' => 'required|exists:users,id',
            'object_id' => 'required|exists:project_objects,id',

            'planned_date_to' => 'required|after_or_equal:' . $afterThisDate,

            'reason' => 'required|string|max:250',

            'comment' => 'required|string|max:250',

            'materials_to' => 'required|array|min:1',
            'materials_to.*.material_id' => 'required|exists:manual_materials,id',
            'materials_to.*.material_unit' => 'required',
            'materials_to.*.material_count' => 'required',

            'materials_from' => 'required|array|min:1',
            'materials_from.*.material_id' => 'required|exists:manual_materials,id',
            'materials_from.*.material_unit' => 'required',
            'materials_from.*.material_count' => 'required',
            'responsible_RP' => [$userCanCreateOnlyDrafts ? 'required' : 'nullable'],
        ];
    }
}
