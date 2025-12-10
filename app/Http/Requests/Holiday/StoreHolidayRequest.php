<?php

namespace App\Http\Requests\Holiday;

use App\Enums\HolidayType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('holiday.write');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:holidays,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(HolidayType::class)],

            // Fixed date holidays
            'fixed_month' => ['required_if:type,fixed', 'nullable', 'integer', 'min:1', 'max:12'],
            'fixed_day' => ['required_if:type,fixed', 'nullable', 'integer', 'min:1', 'max:31'],

            // Easter-relative holidays
            'easter_offset_days' => ['required_if:type,easter_relative', 'nullable', 'integer', 'min:-100', 'max:100'],

            // Computed holidays
            'computation_rule' => ['required_if:type,computed', 'nullable', 'array'],
            'computation_rule.month' => ['required_with:computation_rule', 'integer', 'min:1', 'max:12'],
            'computation_rule.day' => ['required_with:computation_rule', 'string', Rule::in([
                'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday',
            ])],
            'computation_rule.nth' => ['nullable', 'integer', 'min:1', 'max:5'],
            'computation_rule.last' => ['nullable', 'boolean'],

            // Categorization
            'is_holy_day_of_obligation' => ['boolean'],
            'is_universal' => ['boolean'],
            'is_moveable' => ['boolean'],

            // Liturgical info
            'liturgical_color' => ['nullable', 'string', Rule::in([
                'white', 'red', 'green', 'violet', 'rose', 'black',
            ])],
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
            'slug.unique' => 'A holiday with this slug already exists.',
            'fixed_month.required_if' => 'The month is required for fixed date holidays.',
            'fixed_day.required_if' => 'The day is required for fixed date holidays.',
            'easter_offset_days.required_if' => 'The Easter offset days is required for Easter-relative holidays.',
            'computation_rule.required_if' => 'The computation rule is required for computed holidays.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && ! $this->has('slug')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Set defaults
        if (! $this->has('is_holy_day_of_obligation')) {
            $this->merge(['is_holy_day_of_obligation' => false]);
        }

        if (! $this->has('is_universal')) {
            $this->merge(['is_universal' => true]);
        }

        if (! $this->has('is_moveable')) {
            // Auto-set is_moveable based on type
            $type = $this->input('type');
            $this->merge([
                'is_moveable' => $type !== HolidayType::Fixed->value,
            ]);
        }
    }
}
