<?php

namespace App\Http\Requests\ProjectRequest;

use App\Models\WorkVolume\WorkVolume;
use Illuminate\Foundation\Http\FormRequest;

class WorkVolumeReqRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    //    protected $redirectRoute = 'request_error';

    protected function prepareForValidation(): void
    {
        $duplicate_pile = false;
        $duplicate_tongue = false;
        $project_id = preg_replace('/[^0-9]/', '', $this->getUri());

        if ($this->add_tongue == 1) {
            $duplicate_tongue = WorkVolume::whereProjectId($project_id)->whereType(0)->whereOption($this->option_tongue)->exists();
        }

        if ($this->add_pile == 1) {
            $duplicate_pile = WorkVolume::whereProjectId($project_id)->whereType(1)->whereOption($this->option_pile)->exists();
        }

        $this->merge(['duplicate_pile' => $duplicate_pile, 'duplicate_tongue' => $duplicate_tongue]);
    }

    public function messages(): array
    {
        return [
            'duplicate_tongue.not_in' => 'Шпунтовой объем работ с таким наименованием уже существует.',
            'duplicate_pile.not_in' => 'Свайный объем работ с таким наименованием уже существует.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tongue_description' => ['required_with:add_tongue', 'required_without:pile_description', 'max:65530'],
            'pile_description' => ['required_with:add_pile', 'required_without:tongue_description', 'max:65530'],
            'tongue_documents' => 'max:10',
            'tongue_documents.*' => '',
            'pile_documents' => 'max:10',
            'pile_documents.*' => '',
            'option_tongue' => '',
            'option_pile' => '',
            'duplicate_tongue' => ['nullable', 'boolean', 'not_in:'.true],
            'duplicate_pile' => ['nullable', 'boolean', 'not_in:'.true],
            // 'work_volume_pile_id',
            // 'work_volume_tongue_id'
        ];
    }
}
