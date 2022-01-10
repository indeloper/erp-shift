<?php

namespace App\Http\Requests\BrigadeRequests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\HumanResources\Brigade;
use Illuminate\Validation\ValidationException;

class BrigadeDeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_brigade_delete'));
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
