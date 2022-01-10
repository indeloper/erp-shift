<?php

namespace App\Http\Requests\BrigadeRequests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\HumanResources\Brigade;
use Illuminate\Validation\ValidationException;

class BrigadeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_brigade_update'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    public function withValidator($validator)
    {
        if ($this->foreman_id) {
            $user = User::find($this->foreman_id);
            if (! $this->skip_other_brigade_check and $user->brigade_id) {
                $brigade = Brigade::find($user->brigade_id);
                $validator->errors()->add('user_in_other_brigade', "{$brigade->number}");
                throw new ValidationException($validator);
            }

            $brigade = Brigade::where('foreman_id', $this->foreman_id)->where('id', '!=', $this->brigade_id)->first();
            if (! $this->skip_other_brigade_foreman_check and $brigade) {
                $validator->errors()->add('foreman_in_other_brigade', "{$brigade->number}");
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
            'number' => ['required', 'integer', "unique:brigades,number,{$this->brigade_id}"],
            'direction' => ['required', 'in:' . implode(',', array_keys(Brigade::DIRECTIONS))],
            'foreman_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
