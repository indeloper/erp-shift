<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Illuminate\Foundation\Http\FormRequest;

use \Carbon\Carbon;

class CreateWriteOffRequest extends FormRequest
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

    public function messages()
    {
        return [
            'count_files.min' => 'Необходимо прикрепить к операции как минимум один документ'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userCanCreateOnlyDrafts = boolval(! auth()->user()->hasPermission('mat_acc_write_off_create') and auth()->user()->hasPermission('mat_acc_write_off_draft_create') and $this->responsible_RP != 'old_operation');

        // can create operation with back date
        if (auth()->user()->hasPermission('mat_acc_write_off_create')) {
            $afterThisDate = Carbon::now()->subDays(60)->format('d.m.Y');
        } else {
            $afterThisDate = Carbon::today()->format('d.m.Y');
        }

        return [
            'responsible_user_id' => 'required|exists:users,id',
            'object_id' => 'required|exists:project_objects,id',

            'planned_date_to' => 'required|after_or_equal:' . $afterThisDate,

            'reason' => 'required|string|max:250',

            'count_files' => 'required|numeric|min:1',

            'comment' => 'required|string|max:250',


            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:manual_materials,id',
            'materials.*.material_unit' => 'required',
            'materials.*.material_count' => 'required',
            'responsible_RP' => [$userCanCreateOnlyDrafts ? 'required' : 'nullable'],
        ];
    }
}
