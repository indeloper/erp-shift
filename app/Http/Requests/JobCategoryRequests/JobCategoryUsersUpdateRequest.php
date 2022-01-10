<?php

namespace App\Http\Requests\JobCategoryRequests;

use App\Models\HumanResources\JobCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class JobCategoryUsersUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_job_category_users_update'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
            'deleted_user_ids' => ['nullable', 'array'],
            'deleted_user_ids.*' => ['required', 'exists:users,id'],
        ];
    }
}
