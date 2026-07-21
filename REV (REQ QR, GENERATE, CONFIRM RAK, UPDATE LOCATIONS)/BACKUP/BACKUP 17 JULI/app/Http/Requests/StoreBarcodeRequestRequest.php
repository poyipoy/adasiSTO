<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBarcodeRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authenticated user is enough (role checked via middleware)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'material_code' => [
                'required',
                'string',
                Rule::exists('master_materials', 'material_code')->where('is_active', true)
            ],
            'shape_code' => ['required', 'string', Rule::in(['RF', 'RR'])],
            
            // RF (Flat) dimensions
            'thickness' => ['required_if:shape_code,RF', 'nullable', 'integer', 'min:1'],
            'width' => ['required_if:shape_code,RF', 'nullable', 'integer', 'min:1'],
            
            // RR (Round) dimensions (diameter)
            'diameter' => ['required_if:shape_code,RR', 'nullable', 'integer', 'min:1'],
            
            // Length is required for both
            'length' => ['required', 'integer', 'min:1'],
            
            'lot_number' => ['required', 'string', 'max:255'],
            'plant_id' => [
                'required',
                Rule::exists('plants', 'id')->where('is_active', true)
            ],
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('plant_id', $this->input('plant_id'))
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nullify irrelevant dimensions based on shape code
        if ($this->shape_code === 'RF') {
            $this->merge([
                'diameter' => null,
            ]);
        } elseif ($this->shape_code === 'RR') {
            $this->merge([
                'thickness' => null,
                'width' => null,
            ]);
        }

        if ($this->input('lot_number') === null || trim((string) $this->input('lot_number')) === '') {
            $this->merge([
                'lot_number' => '-',
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'material_code.required' => 'Nama Material wajib diisi.',
            'material_code.exists' => 'Material tidak ditemukan atau tidak aktif.',
            'shape_code.required' => 'Jenis wajib dipilih.',
            'shape_code.in' => 'Jenis harus Flat atau Round.',
            'thickness.required_if' => 'Thickness wajib diisi untuk jenis Flat.',
            'thickness.min' => 'Thickness harus lebih dari 0.',
            'width.required_if' => 'Width wajib diisi untuk jenis Flat.',
            'width.min' => 'Width harus lebih dari 0.',
            'diameter.required_if' => 'Diameter wajib diisi untuk jenis Round.',
            'diameter.min' => 'Diameter harus lebih dari 0.',
            'length.required' => 'Length wajib diisi.',
            'length.min' => 'Length harus lebih dari 0.',
            'lot_number.required' => 'Lot Number wajib diisi.',
            'plant_id.required' => 'Plant wajib dipilih.',
            'plant_id.exists' => 'Plant tidak valid atau tidak aktif.',
            'location_id.required' => 'Lokasi wajib dipilih.',
            'location_id.exists' => 'Lokasi tidak valid atau tidak sesuai dengan Plant yang dipilih.',
        ];
    }
}
