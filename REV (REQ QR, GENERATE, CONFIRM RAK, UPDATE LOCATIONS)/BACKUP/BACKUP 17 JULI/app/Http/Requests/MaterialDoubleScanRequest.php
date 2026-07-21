<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class MaterialDoubleScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessMaterialDouble() === true;
    }

    public function rules(): array
    {
        return [
            'barcode_material' => ['required', 'string', 'max:255'],
            'plant_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('plants', 'id')->where('is_active', true)],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'qr' => ['required', 'string', 'max:150'],
            'scan_source' => ['nullable', 'string', 'in:camera,camera-select,scanner_gun,manual'],
            'force_save' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('plant_id') || !$this->filled('location_id')) {
                return;
            }

            $locationMatchesPlant = Location::query()
                ->whereKey((int) $this->input('location_id'))
                ->where('plant_id', (int) $this->input('plant_id'))
                ->exists();

            if (!$locationMatchesPlant) {
                $validator->errors()->add('location_id', 'Location tidak sesuai dengan Plant.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'barcode_material' => strtoupper(trim((string) $this->input('barcode_material'))),
            'qr' => trim((string) $this->input('qr')),
        ]);
    }
}
