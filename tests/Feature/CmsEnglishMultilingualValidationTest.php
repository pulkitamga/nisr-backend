<?php

namespace Tests\Feature;

use App\Traits\ValidatesCmsEnglishMultilingualInput;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CmsEnglishMultilingualValidationTest extends TestCase
{
    public function test_trait_rejects_blank_english_plain_text_field(): void
    {
        $validator = new class {
            use ValidatesCmsEnglishMultilingualInput;

            public function validate(Request $request, array $fields): void
            {
                $this->validateRequiredCmsEnglishFields($request, $fields);
            }
        };

        $request = Request::create('/cms-test', 'POST', [
            'lang' => ['ar', 'en'],
            'heading' => ['عنوان', ''],
        ]);

        try {
            $validator->validate($request, [
                'heading' => ['message' => 'The_heading_in_english_is_required'],
            ]);
            $this->fail('Expected validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                translate('The_heading_in_english_is_required'),
                $exception->errors()['heading.1'][0] ?? null
            );
        }
    }

    public function test_trait_rejects_blank_english_rich_text_field(): void
    {
        $validator = new class {
            use ValidatesCmsEnglishMultilingualInput;

            public function validate(Request $request, array $fields): void
            {
                $this->validateRequiredCmsEnglishFields($request, $fields);
            }
        };

        $request = Request::create('/cms-test', 'POST', [
            'lang' => ['ar', 'en'],
            'description' => ['<p>وصف</p>', '<p><br></p>'],
        ]);

        try {
            $validator->validate($request, [
                'description' => ['message' => 'The_description_in_english_is_required', 'rich_text' => true],
            ]);
            $this->fail('Expected validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                translate('The_description_in_english_is_required'),
                $exception->errors()['description.1'][0] ?? null
            );
        }
    }
}
