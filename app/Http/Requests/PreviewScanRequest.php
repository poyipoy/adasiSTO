<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isScanner() === true;
    }

    public function rules(): array
    {
        return [
            'qr' => ['required', 'string', 'max:150'],
        ];
    }
}
