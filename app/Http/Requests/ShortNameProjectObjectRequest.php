<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShortNameProjectObjectRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_first'       => 'required|string',
            'name_second'      => 'required|string',
            'index'            => 'required|string',
            'city'             => 'required|string',
            'cadastral_number' => 'required|string',
            'street'           => 'required|string',
            'region'           => 'required|string',
            'house'            => 'required|string',
            'body'             => 'required|string',
            'literature'       => 'required|string',
            'building'         => 'required|string',
            'land_plot'        => 'required|string',
            'queue'            => 'required|string',
            'lot'              => 'required|string',
            'stage'            => 'required|string',
            'array'            => 'required|string',
        ];
    }

}
