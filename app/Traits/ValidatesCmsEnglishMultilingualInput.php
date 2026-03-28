<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesCmsEnglishMultilingualInput
{
    protected function validateRequiredCmsEnglishFields(Request $request, array $fields): void
    {
        $englishIndex = getLanguageInputIndex($request, 'en');
        if ($englishIndex === null) {
            return;
        }

        $errors = [];

        foreach ($fields as $field => $options) {
            $value = $request->input($field . '.' . $englishIndex);
            $isBlank = ($options['rich_text'] ?? false)
                ? richTextToPlainText($value) === ''
                : trim((string) ($value ?? '')) === '';

            if ($isBlank) {
                $errors[$field . '.' . $englishIndex] = [translate($options['message'])];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
