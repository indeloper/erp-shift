<?php

namespace App\Http\Requests\HumanAccountingReportRequests;

use Illuminate\Foundation\Http\FormRequest;

class WorkTimeReportGenerateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) auth()->user()->hasPermission('human_resources_work_time_report_generate');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'date' => ['required', 'string', 'min:7', 'max:21'],
        ];
    }
}
