<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isScanner() === true;
    }

    public function rules(): array
    {
        return [
            'plant_id' => ['required', 'integer', Rule::exists('plants', 'id')->where('is_active', true)],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('locations', 'name')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)
                    ->where('plant_id', $this->integer('plant_id'))),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }
}
