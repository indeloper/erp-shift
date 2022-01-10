<?php

namespace App\Http\Requests\ReportGroupRequests;

use App\Models\HumanResources\{JobCategory, ReportGroup};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReportGroupStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_report_group_create'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    public function withValidator($validator)
    {
        if (ReportGroup::where('name', $this->request->get('name'))->exists()) {
            $validator->errors()->add('name', 'Отчётная группа с таким названием уже существует');
            throw new ValidationException($validator);
        }

        if ($this->job_categories and ! $this->skip_job_categories_check) {
            $jobCategoriesThatHasReportGroup = [];
            $jobCategories = JobCategory::whereIn('id', $this->request->get('job_categories'))->get();
            foreach ($jobCategories as $jobCategory) {
                if ($jobCategory->report_group_id) $jobCategoriesThatHasReportGroup[] = $jobCategory->id;
            }
            if ($jobCategoriesThatHasReportGroup) {
                $validator->errors()->add('override', json_encode($jobCategoriesThatHasReportGroup));
                throw new ValidationException($validator);
            }
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
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'job_categories' => ['nullable', 'array'],
            'job_categories.*' => ['required', 'exists:job_categories,id'],
        ];
    }
}
