<?php

namespace App\Http\Requests\BrigadeRequests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\HumanResources\Brigade;
use Illuminate\Validation\ValidationException;

class BrigadeUsersUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_brigade_users_update'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    public function withValidator($validator)
    {
        if (! $this->skip_users_check) {
            $usersThatHasBrigade = [];
            $users = User::whereIn('id', $this->request->get('user_ids'))->get();
            foreach ($users as $user) {
                if ($user->brigade_id) {
                    if ($user->brigade_id != $this->request->get('brigade_id')) {
                        $usersThatHasBrigade[] = $user->id;
                    }
                } else if ($user->brigades()->exists()) {
                    $validator->errors()->add('add_foreman_as_brigade_user', json_encode([$user->id]));
                    throw new ValidationException($validator);
                }
            }
            if ($usersThatHasBrigade) {
                $validator->errors()->add('override', json_encode($usersThatHasBrigade));
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
            'brigade_id' => ['required', 'exists:brigades,id'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
            'deleted_user_ids' => ['nullable', 'array'],
            'deleted_user_ids.*' => ['required', 'exists:users,id'],
        ];
    }
}
