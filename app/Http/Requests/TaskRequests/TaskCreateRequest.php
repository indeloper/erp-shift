<?php

namespace App\Http\Requests\TaskRequests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TaskCreateRequest extends FormRequest
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
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:250',
            'contractor_id' => 'required|exists:contractors,id',
            'project_id' => $this->project_id ? 'exists:projects,id' : '',
            'responsible_user_id' => 'required|exists:users,id',
            'documents' => 'max:10',
            'documents.*' => 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,dwg,dwl,dwl2,dxf,mpp,gif,bmp,txt,rtf,pptx',
            'expired_at' => 'required|date|after:'.Carbon::now()->addMinutes(30),
        ];
    }
}
