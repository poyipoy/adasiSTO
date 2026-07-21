<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckDuplicateScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isScanner() === true;
    }

    public function rules(): array
    {
        return [
            'barcode_material' => ['required', 'string', 'max:150'],
        ];
    }
}
