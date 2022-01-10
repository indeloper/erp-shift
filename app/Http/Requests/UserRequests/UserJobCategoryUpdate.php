<?php

namespace App\Http\Requests\UserRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserJobCategoryUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->can('user_job_category_change'));
    }

    public function rules()
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'job_category_id' => ['required', 'exists:job_categories,id']
        ];
    }
}
