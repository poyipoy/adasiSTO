<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isScanner() === true;
    }

    public function rules(): array
    {
        return [
            'qr' => ['required', 'string', 'max:150'],
            'plant_id' => [
                'required',
                'integer',
                Rule::exists('plants', 'id')->where(fn ($query) => $query->where('is_active', true))
            ],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'scan_source' => ['nullable', 'string', 'in:camera,camera-select,scanner_gun,manual'],
            'force_save' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled(['plant_id', 'location_id'])) {
                return;
            }

            $locationBelongsToPlant = \App\Models\Location::query()
                ->where([
                    'id' => $this->integer('location_id'),
                    'user_id' => $this->user()->id,
                    'plant_id' => $this->integer('plant_id'),
                    'is_active' => true,
                ])
                ->exists();

            if (!$locationBelongsToPlant) {
                $validator->errors()->add('location_id', 'Location tidak valid untuk plant yang dipilih.');
            }
        });
    }
}
