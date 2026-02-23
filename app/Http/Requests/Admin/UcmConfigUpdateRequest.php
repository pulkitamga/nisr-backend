<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UcmConfigUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'api_version' => 'nullable|string|max:20',
            'report_url' => 'nullable|string|max:255',
            'webhook_token' => 'nullable|string|max:255',
            'ca_path' => 'nullable|string|max:255',
            'status' => 'nullable|in:1',
            'digest' => 'nullable|in:1',
            'verify_tls' => 'nullable|in:1',
        ];
    }
}
