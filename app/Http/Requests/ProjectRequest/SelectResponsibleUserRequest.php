<?php

namespace App\Http\Requests\ProjectRequest;

use App\Traits\UserSearchByGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SelectResponsibleUserRequest extends FormRequest
{
    use UserSearchByGroup;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        //        return in_array(Auth::user()->group_id, ['50'/*'7'*/, '53'/*'16'*/, '8'/*'5'*/, '54'/*'26'*/, '49'/*'32'*/, '49'/*'35'*/, '5', '6']);
        //        return in_array(auth()->user()->group_id, $this->findAllUsersAndReturnGroupIds([50, 53, 8, 54, 49]));
    }

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role' => 'required|string|max:2',
            'user' => 'required|string',
        ];
    }
}
