<?php

namespace App\Traits;

use Illuminate\Validation\Validator;

trait ValidatesEnglishMultilingualInput
{
    protected function getEnglishInputIndex(): ?int
    {
        return getLanguageInputIndex($this, 'en');
    }

    protected function getEnglishFieldKey(string $field): string
    {
        return $field . '.' . ($this->getEnglishInputIndex() ?? 0);
    }

    protected function validateEnglishMultilingualFields(Validator $validator, array $fields): void
    {
        $englishIndex = $this->getEnglishInputIndex();

        if ($englishIndex === null) {
            return;
        }

        foreach ($fields as $field => $options) {
            $message = $options['message'] ?? translate('The_name_in_english_is_required') . '!';
            $richText = (bool)($options['rich_text'] ?? false);
            $value = $this->input($field . '.' . $englishIndex);

            $isBlank = $richText
                ? richTextToPlainText($value) === ''
                : trim((string)($value ?? '')) === '';

            if ($isBlank) {
                $validator->errors()->add($field . '.' . $englishIndex, $message);
            }
        }
    }
}
