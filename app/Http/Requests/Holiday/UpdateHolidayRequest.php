<?php

namespace App\Http\Requests\Holiday;

use App\Enums\HolidayType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('holidays.write');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $holidayId = $this->route('holiday')?->id ?? $this->route('holiday');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('holidays', 'slug')->ignore($holidayId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['sometimes', 'required', Rule::enum(HolidayType::class)],

            // Fixed date holidays
            'fixed_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'fixed_day' => ['nullable', 'integer', 'min:1', 'max:31'],

            // Easter-relative holidays
            'easter_offset_days' => ['nullable', 'integer', 'min:-100', 'max:100'],

            // Computed holidays
            'computation_rule' => ['nullable', 'array'],
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
        ];
    }
}
