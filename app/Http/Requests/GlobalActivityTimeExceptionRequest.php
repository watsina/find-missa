<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class GlobalActivityTimeExceptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['string', 'required', 'min:6', 'max:255'],
            'message' => ['string', 'nullable', 'min:10'],
            'start_date' => ['date', 'required'],
            'end_date' => ['date', 'required', 'after_or_equal:start_date'],
        ];
    }
}
