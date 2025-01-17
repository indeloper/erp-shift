<?php

namespace App\Http\Requests\Notification;

use App\Domain\Enum\NotificationSortType;
use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sort_selector' => ['sometimes', 'in:'.implode(',', NotificationSortType::sorts())],
            'sort_direction' => ['sometimes', 'in:desc,asc'],
        ];
    }
}
