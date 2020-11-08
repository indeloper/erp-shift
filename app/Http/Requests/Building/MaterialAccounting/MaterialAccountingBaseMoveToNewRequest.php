<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use App\Models\MatAcc\MaterialAccountingBase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class MaterialAccountingBaseMoveToNewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('mat_acc_base_move_to_new'));
    }

    public function withValidator($validator) {
        $base = MaterialAccountingBase::where('used', 1)->find($this->get('base_id'));
        if (! $base) {
            $validator->errors()->add("base_id", 'Запись не существует');
            throw new ValidationException($validator);
        } else if (round($base->count, 3) < $this->get('count')) {
            $validator->errors()->add("too_much", 'На базе нет такого количества материала');
            throw new ValidationException($validator);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'base_id' => ['required', 'exists:material_accounting_bases,id'],
            'count' => ['required', 'gt:0'],
        ];
    }
}
