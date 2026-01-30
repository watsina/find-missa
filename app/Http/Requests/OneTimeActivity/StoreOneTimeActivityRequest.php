<?php

namespace App\Http\Requests\OneTimeActivity;

use App\Models\Church;
use App\Enums\ActivityType;
use App\Rules\ExistsInTeam;
use Illuminate\Validation\Rule;
use App\Enums\ActivityTypeDescription;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateActivityTypeDescription;

class StoreOneTimeActivityRequest extends FormRequest
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
            'church_id' => ['required', 'integer', new ExistsInTeam(Church::class)],
            'type' => ['required', Rule::enum(ActivityType::class)],
            'activity_type_description' => ['nullable', Rule::enum(ActivityTypeDescription::class), new ValidateActivityTypeDescription],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'language' => ['required', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:255'],

            // Tags
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:activity_tags,id'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
