<?php

namespace App\Http\Requests\Admin\OnlineExam;

use Illuminate\Foundation\Http\FormRequest;

class SmQuestionBankRequest extends FormRequest
{

    protected $stopOnFirstFailure = true;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $maxFileSize = generalSetting()->file_size * 1024;
        $question_image_rule = !empty($this->route('id')) ? 'nullable|mimes:jpg,jpeg,png|max:' . $maxFileSize : 'required_if:question_type,MI|mimes:jpg,jpeg,png|max:' . $maxFileSize;
        $question_video_rule = !empty($this->route('id')) ? 'nullable|mimes:mp4|max:' . $maxFileSize : 'required_if:question_type,VI|mimes:mp4|max:' . $maxFileSize;

        $rules = [
            'group' => 'required',
            'question' => 'required',
            'question_type' => 'required',
            'marks' => 'required',
            'number_of_option' => 'required_if:question_type,M',
            'answer_type' => 'required_if:question_type,MI',
            'question_image' => $question_image_rule,
            'question_video' => $question_video_rule,
            'number_of_optionImg' => 'required_if:question_type,MI',
            'trueOrFalse' => 'required_if:question_type,T|in:T,F',
            'suitable_words' => 'required_if:question_type,F',
            'match_questions' => 'required_if:question_type,MT|array',
            'match_questions.*' => 'required_if:question_type,MT',
            'match_answers' => 'required_if:question_type,MT|array',
            'match_answers.*' => 'required_if:question_type,MT',
        ];

        if (moduleStatusCheck('University')) {      // University Module
            $rules['un_semester_label_id'] = 'required';
            if ($this->id) {
                $rules['un_section_id'] = 'required';
            } else {
                $rules['un_section_ids'] = 'required';
            }
        } else {                                    // School Module or General
            $rules['class'] = 'required';
            $rules['section'] = 'required';
            $rules['section.*'] = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'match_questions.*.required' => __('exam.all_match_questions_required'),
            'match_answers.*.required' => __('exam.all_match_answers_required'),
            'match_questions.*.array' => __('exam.all_match_questions_array'),
            'match_answers.*.array' => __('exam.all_match_answers_array'),
        ];
    }
}
