<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialDoubleGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessMaterialDouble() === true;
    }

    public function rules(): array
    {
        return [
            'barcode_material' => ['required', 'string', 'max:255'],
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(fn ($query) => $query
                    ->where('plant_id', $this->integer('plant_id'))
                    ->where('is_active', true)),
            ],
            'sto_code' => ['nullable', 'string', 'max:255'],
            'material_code' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }
}
