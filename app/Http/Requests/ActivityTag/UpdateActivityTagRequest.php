<?php

namespace App\Http\Requests\ActivityTag;

use Illuminate\Validation\Rule;
use App\Enums\ActivityTagCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $activityTag = $this->route('activity_tag');

        // System tags cannot be updated
        if ($activityTag && $activityTag->is_system) {
            return false;
        }

        return $this->user()->can('activity-tag.write');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $activityTagId = $this->route('activity_tag')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('activity_tags', 'slug')->ignore($activityTagId),
            ],
            'category' => ['sometimes', 'required', Rule::enum(ActivityTagCategory::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
            'slug.unique' => 'A tag with this slug already exists.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #FF5733).',
        ];
    }
}
