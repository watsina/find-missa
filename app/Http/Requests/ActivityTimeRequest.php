<?php

namespace App\Http\Requests;

use App\Enums\ActivityType;
use Illuminate\Validation\Rule;
use App\Enums\ActivityOccurrence;
use Illuminate\Foundation\Http\FormRequest;

class ActivityTimeRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ActivityType::class)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'language' => ['required', 'string', 'max:100'],
            'church_id' => ['nullable', 'integer', 'exists:churches,id'],
            'occurrence' => ['required', Rule::enum(ActivityOccurrence::class)],
            'date' => ['required_if:occurrence,once', 'date'],
            'day_of_week' => ['required_if:occurrence,weekly'],
        ];
    }

    public function messages()
    {
        return [
            'end_time.after' => 'The end time should be a time after the start time',
        ];
    }
}
