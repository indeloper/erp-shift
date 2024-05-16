<?php

namespace App\Http\Requests\Building\TechAccounting\FuelTank;

use App\Models\FileEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreFuelTankOperation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function withValidator($validator)
    {
        if (! $this->requestHasTwoVideos()) {
            $validator->errors()->add('videos', 'Необходимо прикрепить как минимум 2 видео');
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
            'fuel_tank_id' => 'required',
            'object_id' => 'required',
            'our_technic_id' => 'required_if:type,2',
            'contractor_id' => 'required_if:type,1',
            'value' => 'required|gt:0',
            'type' => 'required|in:1,2',
            'description' => 'required|string',
            'operation_date' => 'required|date',
            'owner_id' => 'required_if:type,1',
        ];
    }

    private function requestHasTwoVideos()
    {
        if ($this->request->has('file_ids')) {
            $videos_count = FileEntry::whereIn('id', $this->request->get('file_ids'))->where('mime', 'like', '%video%')->count();

            return $videos_count >= 2;
        }

        return false;
    }
}
