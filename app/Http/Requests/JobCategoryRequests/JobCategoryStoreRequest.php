<?php

namespace App\Http\Requests\JobCategoryRequests;

use App\Models\HumanResources\JobCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class JobCategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_job_category_create'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);

        if ($this->tariffs) {
            $tariffs = $this->tariffs;
            foreach ($tariffs as $key => $tariff) {
                $tariffs[$key]['user_id'] = auth()->id();
            }
            $this->merge([
                'tariffs' => $tariffs
            ]);
        }
    }

    public function withValidator($validator)
    {
        if (JobCategory::where('name', $this->request->get('name'))->exists()) {
            $validator->errors()->add('name', 'Должностная категория с таким названием уже существует');
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
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'min:5', 'max:100'],
            'tariffs' => ['nullable', 'array', 'max:12'],
            'tariffs.*.tariff_id' => ['required', 'exists:tariff_rates,id'],
            'tariffs.*.rate' => ['required', 'numeric'],
            'tariffs.*.user_id' => ['required', 'exists:users,id'],
        ];
    }
}