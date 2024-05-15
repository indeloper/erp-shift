<?php

namespace App\Http\Requests\ProjectRequest;

use App\Models\CommercialOffer\CommercialOffer;
use Illuminate\Foundation\Http\FormRequest;

class CommercialOfferReqRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $duplicate = false;
        $project_id = preg_replace('/[^0-9]/', '', $this->getUri());

        if ($this->axios and $this->com_offer_id_pile == 'new' and $this->com_offer_id_tongue == 'new') {
            $duplicate = CommercialOffer::where('project_id', $project_id)->where('is_tongue', $this->is_tongue)->where('option', $this->option)->exists();
        }

        $this->merge(['duplicate' => $duplicate]);
    }

    public function messages(): array
    {
        return [
            'duplicate.not_in' => 'Коммерческое предложение с таким наименованием уже существует.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'duplicate' => ['nullable', 'boolean', 'not_in:'.true],
        ];
    }
}
