<?php

namespace App\Http\Requests;

class DeleteMaterialDoubleRequest extends MaterialDoubleGroupRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:scan_results,id'],
        ]);
    }
}
