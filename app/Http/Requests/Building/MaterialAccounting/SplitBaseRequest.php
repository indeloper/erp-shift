<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use App\Models\MatAcc\MaterialAccountingBase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * @property int comment_id
 * @property string unit
 * @property string mode
 * @property float count
 * @property array comments
 * @property int mat_to_unite
 */
class SplitBaseRequest extends FormRequest
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

    public function withValidator($validator)
    {
        $base = MaterialAccountingBase::find($this->get('comment_id'));
        $converted_count = ($base->count * $base->material->getConvertValueFromTo($base->unit, $this->get('unit'))).'';

        if ($converted_count < $this->get('count')) {
            $validator->errors()->add('count', 'На объекте недостаточно материала');
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
            'comment_id' => 'required|exists:material_accounting_bases,id',
            'unit' => 'required|string',
            'mode' => 'required|in:split,unite',
            'count' => 'required',
            'mat_to_unite' => 'sometimes|required|exists:material_accounting_bases,id',
        ];
    }
}
