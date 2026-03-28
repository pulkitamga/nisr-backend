<?php

namespace App\Http\Requests\Admin;

use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;

class HelpTopicAddRequest extends FormRequest
{
    use ValidatesEnglishMultilingualInput;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $englishQuestionKey = $this->getEnglishFieldKey('question');
        $englishAnswerKey = $this->getEnglishFieldKey('answer');

        return [
            'question' => 'required|array',
            'answer' => 'required|array',
            'ranking' => 'required',
            $englishQuestionKey => 'required|string',
            $englishAnswerKey => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => translate('the_question_field_is_required'),
            $this->getEnglishFieldKey('question') . '.required' => translate('The_question_in_english_is_required'),
            'answer.required' => translate('the_answer_field_is_required'),
            $this->getEnglishFieldKey('answer') . '.required' => translate('The_answer_in_english_is_required'),
            'ranking.required' => translate('the_ranking_field_is_required'),
        ];
    }

}
