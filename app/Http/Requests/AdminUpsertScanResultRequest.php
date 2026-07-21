<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpsertScanResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'scanner')],
            'plant_id' => ['required', 'integer', Rule::exists('plants', 'id')->where('is_active', true)],
            'location_name' => ['required', 'string', 'max:100'],
            'barcode_raw' => ['required', 'string', 'max:150'],
            'barcode_material' => ['required', 'string', 'max:100'],
            'lot_number' => ['required', 'string', 'max:100'],
            'qty' => ['required', 'integer', 'min:1'],
            'material_code' => ['required', 'string', Rule::exists('master_materials', 'material_code')->where('is_active', true)],
            'material_name' => ['required', 'string', 'max:255'],
            'shape_code' => ['required', 'string', Rule::in(['RF', 'RR', 'RH'])],
            'shape_name' => ['required', 'string', Rule::in(['Flat', 'Round', 'Hollow'])],
            'thickness' => ['nullable', 'integer', 'min:1'],
            'width' => ['nullable', 'integer', 'min:1'],
            'diameter' => ['nullable', 'integer', 'min:1'],
            'length' => ['required', 'integer', 'min:1'],
            'keterangan' => ['required', 'string', Rule::exists('master_keterangan', 'name')->where('is_active', true)],
            'scan_source' => ['nullable', 'string', 'max:50'],
            'created_at' => ['required', 'date'],
            'force_save' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $shapeCode = $this->input('shape_code');
            $shapeName = $this->input('shape_name');

            if ($shapeCode === 'RF' && $shapeName !== 'Flat') {
                $validator->errors()->add('shape_name', 'Shape RF harus menggunakan nama Flat.');
            }

            if ($shapeCode === 'RH' && $shapeName !== 'Hollow') {
                $validator->errors()->add('shape_name', 'Shape RH harus menggunakan nama Hollow.');
            }

            if ($shapeCode === 'RR' && $shapeName !== 'Round') {
                $validator->errors()->add('shape_name', 'Shape RR harus menggunakan nama Round.');
            }

            if (in_array($shapeCode, ['RF', 'RH'])) {
                if (!$this->filled('thickness')) {
                    $validator->errors()->add('thickness', "Thickness wajib diisi untuk shape {$shapeName}.");
                }

                if (!$this->filled('width')) {
                    $validator->errors()->add('width', "Width wajib diisi untuk shape {$shapeName}.");
                }

                if ($this->filled('diameter')) {
                    $validator->errors()->add('diameter', "Diameter harus kosong untuk shape {$shapeName}.");
                }
            }

            if ($shapeCode === 'RR') {
                if (!$this->filled('diameter')) {
                    $validator->errors()->add('diameter', 'Diameter wajib diisi untuk shape Round.');
                }

                if ($this->filled('thickness')) {
                    $validator->errors()->add('thickness', 'Thickness harus kosong untuk shape Round.');
                }

                if ($this->filled('width')) {
                    $validator->errors()->add('width', 'Width harus kosong untuk shape Round.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_name' => trim((string) $this->input('location_name')),
            'barcode_raw' => trim((string) $this->input('barcode_raw')),
            'barcode_material' => strtoupper(trim((string) $this->input('barcode_material'))),
            'lot_number' => trim((string) $this->input('lot_number')) !== '' ? trim((string) $this->input('lot_number')) : '-',
            'material_code' => strtoupper(trim((string) $this->input('material_code'))),
            'material_name' => trim((string) $this->input('material_name')),
            'shape_code' => strtoupper(trim((string) $this->input('shape_code'))),
            'shape_name' => trim((string) $this->input('shape_name')),
            'scan_source' => trim((string) ($this->input('scan_source') ?: 'admin')),
        ]);
    }
}
