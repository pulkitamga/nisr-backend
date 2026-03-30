<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WarrantyPolicyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'lang' => 'required|array',
            'value' => 'required|array',
            'value.0' => 'required|string',
            'value.*' => 'nullable|string',
            'version' => 'nullable|string',
            'published_at' => 'nullable|date',
        ];
    }
}
