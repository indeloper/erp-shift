<?php

namespace App\Http\Requests\TaskRequests;

use Illuminate\Foundation\Http\FormRequest;

class TaskCallRequest extends FormRequest
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

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function prepareForValidation()
    {
        if ($this->contractor_phone_number != null) {
            $this->merge(['contractor_phone_number' => preg_replace('~[\D]~', '', $this->contractor_phone_number)]);
        }
    }

    public function rules(): array
    {
        return [
            'contractor_full_name' => ($this->contractor_full_name != null ? 'required' : 'nullable').'|max:200|unique:contractors,full_name,'.($this->contractor_id ? $this->contractor_id : ''),
            'contractor_short_name' => 'required_with:contractor_full_name|max:100',
            'contractor_inn' => 'nullable|string|min:10|max:14|unique:contractors,inn,'.($this->contractor_id ? $this->contractor_id : ''),
            'contractor_kpp' => 'nullable|string|size:9|unique:contractors,kpp,'.($this->contractor_id ? $this->contractor_id : ''),
            'contractor_ogrn' => 'nullable|string|min:12|max:15|unique:contractors,ogrn,'.($this->contractor_id ? $this->contractor_id : ''),
            'contractor_legal_address' => 'nullable|string|max:200',
            'contractor_physical_adress' => 'nullable|string|max:200',
            'contractor_general_manager' => 'nullable|string|max:100',
            'contractor_phone_number' => 'nullable|string|min:11|max:11|unique:contractors,phone_number,'.($this->contractor_id ? $this->contractor_id : ''),
            'contractor_email' => 'nullable|max:50|email|unique:contractors,email,'.($this->contractor_id ? $this->contractor_id : ''),

            'contractor_bank_name' => 'nullable|string|max:100',
            'contractor_check_account' => 'nullable|string|size:20',
            'contractor_cor_account' => 'nullable|string|size:20',
            'contractor_bik' => 'nullable|string|size:9',

            'contractor_id' => 'required_with:contractor_full_name',
            'project_id' => 'nullable',

            'contact_id' => 'required_with:contractor_full_name|nullable|exists:contractor_contacts,id',
            'final_note' => 'required|string|max:200',
        ];
    }
}
