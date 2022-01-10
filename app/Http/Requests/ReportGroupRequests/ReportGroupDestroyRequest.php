<?php

namespace App\Http\Requests\ReportGroupRequests;

use App\Models\HumanResources\{JobCategory, ReportGroup};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReportGroupDestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_report_group_destroy'));
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
