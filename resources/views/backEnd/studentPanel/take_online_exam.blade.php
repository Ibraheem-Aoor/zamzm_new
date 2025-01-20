@extends('backEnd.master')
@section('title')
    @lang('exam.take_online_exam')
@endsection
@push('css')
    <style>
        .exam-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            margin: 20px 0;
        }

        .exam-header {
            background: linear-gradient(to right, #2e4ead, #1976D2);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }

        .exam-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .info-item strong {
            display: block;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .timer-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
            text-align: center;
        }

        .instruction-box {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px;
            border-radius: 6px;
            border-left: 4px solid #2e4ead;
        }

        .question-container {
            padding: 20px;
            border-bottom: 1px solid #eee;
            margin: 0 20px;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .question-number {
            font-size: 16px;
            color: #2e4ead;
            font-weight: 600;
        }

        .marks-badge {
            background: #2e4ead;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        .question-content {
            font-size: 15px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .question-image {
            max-width: 100%;
            height: auto;
            margin: 15px 0;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .options-container .common-checkbox {
            display: none;
        }

        .option-item {
            background: #f8f9fa;
            margin: 10px 0;
            padding: 12px 15px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option-item:hover {
            background: #fff;
            border-color: #2e4ead;
        }

        .option-item.selected {
            background: #e3f2fd;
            border-color: #2e4ead;
        }

        .option-item label {
            cursor: pointer;
            margin-bottom: 0;
            width: 100%;
            display: block;
        }

        .matching-option {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }

        .matching-option select,
        .matching-option input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .true-false-container {
            display: flex;
            gap: 15px;
        }

        .true-false-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .true-false-option:hover {
            background: #f8f9fa;
            border-color: #2e4ead;
        }

        .true-false-option.selected {
            background: #e3f2fd;
            border-color: #2e4ead;
        }

        .fill-blank input {
            width: 100%;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-top: 10px;
        }

        .submit-btn {
            background: #2e4ead;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 30px auto;
            display: block;
        }

        .submit-btn:hover {
            background: #1976D2;
            transform: translateY(-1px);
        }

        .upload_grid_wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 15px 0;
        }

        .single_upload_img {
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
        }

        .single_upload_img img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .img_check {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* Preserve original form elements while hiding them visually */
        .common-checkbox,
        .common-radio {
            position: absolute;
            opacity: 0;
        }

        /* Custom checkbox/radio styling */
        .custom-control {
            position: relative;
            padding-left: 25px;
            cursor: pointer;
        }

        .custom-control::before {
            content: '';
            position: absolute;
            left: 0;
            top: 2px;
            width: 18px;
            height: 18px;
            border: 2px solid #2e4ead;
            border-radius: 3px;
        }

        .common-checkbox:checked+.custom-control::after {
            content: '✓';
            position: absolute;
            left: 4px;
            top: -1px;
            color: #2e4ead;
            font-size: 16px;
        }
    </style>
@endpush

@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('exam.examinations')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a href="#">@lang('exam.examinations')</a>
                    <a href="{{ route('student_online_exam') }}">@lang('exam.online_exam')</a>
                    <a href="{{ route('take_online_exam', @$online_exam->id) }}">@lang('exam.take_online_exam')</a>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'student_online_exam_submit', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'online_take_exam']) }}
                    <div class="exam-container">
                        <div class="exam-header">
                            <h1>{{ @$online_exam->title }}</h1>
                            <div class="exam-info-grid">
                                <div class="info-item">
                                    <strong>@lang('common.subject')</strong>
                                    <span>{{ @$online_exam->subject != '' ? @$online_exam->subject->subject_name : '' }}</span>
                                </div>
                                <div class="info-item">
                                    <strong>@lang('common.class_Sec')</strong>
                                    <span>
                                        {{ @$online_exam->class != '' ? @$online_exam->class->class_name : '' }}
                                        ({{ @$online_exam->section != '' ? @$online_exam->section->section_name : '' }})
                                    </span>
                                </div>
                                <div class="info-item">
                                    <strong>@lang('exam.total_marks')</strong>
                                    <span>{{ @$total_marks }}</span>
                                </div>
                            </div>

                            <div class="timer-box">
                                <strong>@lang('exam.time_remaining')</strong>
                                <div id="countDownTimer"></div>
                            </div>
                        </div>

                        <div class="instruction-box">
                            <p><strong>@lang('exam.instruction'): </strong>{{ @$online_exam->instruction }}</p>
                            <p><strong>@lang('exam.exam_has_to_be_submitted_within'): </strong>
                                {{ @$online_exam->date }} {{ @$online_exam->end_time }}
                            </p>
                        </div>

                        <input type="hidden" name="online_exam_id" id="online_exam_id" value="{{ @$online_exam->id }}">
                        <input type="hidden" id="count_date" value="{{ @$online_exam->date }}">
                        <input type="hidden" id="count_start_time"
                            value="{{ date('Y-m-d H:i:s', strtotime(@$online_exam->start_time)) }}">
                        <input type="hidden" id="count_end_time"
                            value="{{ date('Y-m-d H:i:s', strtotime(@$online_exam->end_time)) }}">

                        @php $j=0; @endphp
                        @foreach ($assigned_questions as $question)
                            @php
                                $student_id = Auth::user()->student->id;
                                $submited_answer = App\OnlineExamStudentAnswerMarking::StudentGivenAnswer(
                                    $question->online_exam_id,
                                    $question->question_bank_id,
                                    $student_id,
                                );

                                if ($question->questionBank->type == 'MI') {
                                    $submited_answer = App\OnlineExamStudentAnswerMarking::StudentImageAnswer(
                                        $question->online_exam_id,
                                        $question->question_bank_id,
                                        $student_id,
                                    );
                                }
                            @endphp

                            <div class="question-container">
                                <div class="question-header">
                                    <div class="question-number">
                                        {{ ++$j . '.' }} {{ @$question->questionBank->question }}
                                    </div>
                                    <div class="marks-badge">
                                        {{ @$question->questionBank->marks }} @lang('exam.marks')
                                    </div>
                                </div>

                                <input type="hidden" name="online_exam_id" value="{{ @$question->online_exam_id }}">
                                <input type="hidden" name="question_ids[]" value="{{ @$question->question_bank_id }}">

                                @if (@$question->questionBank->type == 'MI')
                                    <div class="question-content">
                                        <img class="question-image"
                                            src="{{ asset($question->questionBank->question_image) }}" alt="">
                                    </div>
                                @endif

                                @if (@$question->questionBank->type == 'M' || @$question->questionBank->type == 'VI')
                                    <div class="options-container">
                                        @foreach ($question->questionBank->questionMu as $option)
                                            <div class="option-item">
                                                <input type="radio" id="answer{{ @$option->id }}"
                                                    class="common-checkbox answer_question_mu"
                                                    name="options_{{ @$question->question_bank_id }}"
                                                    value="{{ $option->id }}"
                                                    data-question="{{ @$question->question_bank_id }}"
                                                    data-option="{{ @$option->id }}"
                                                    {{ isset($submited_answer) ? ($submited_answer->user_answer == $option->id ? 'checked' : '') : '' }}>
                                                <label for="answer{{ @$option->id }}">{{ @$option->title }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->questionBank->type == 'MI')
                                    <div class="upload_grid_wrapper">
                                        @foreach ($question->questionBank->questionMu as $option)
                                            <div class="single_upload_img">
                                                <div class="img_check">
                                                    <input type="{{ @$question->questionBank->answer_type }}"
                                                        id="answer{{ @$option->id }}"
                                                        class="common-checkbox answer_question_mu"
                                                        name="options_{{ @$question->question_bank_id }}"
                                                        value="{{ $option->id }}"
                                                        data-question="{{ @$question->question_bank_id }}"
                                                        data-option="{{ @$option->id }}"
                                                        {{ isset($submited_answer) ? (in_array($option->id, $submited_answer) ? 'checked' : '') : '' }}>
                                                    <label for="answer{{ @$option->id }}"></label>
                                                </div>
                                                <img src="{{ asset($option->title) }}" alt="">
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->questionBank->type == 'T')
                                    <div class="true-false-container">
                                        <div class="true-false-option">
                                            <input type="radio" id="true_{{ @$question->question_bank_id }}"
                                                class="common-radio answer_question_mu"
                                                name="trueOrFalse_{{ @$question->question_bank_id }}" value="T"
                                                data-question="{{ @$question->question_bank_id }}"
                                                {{ isset($submited_answer) ? ($submited_answer->user_answer == 'T' ? 'checked' : '') : '' }}><label
                                                for="true_{{ @$question->question_bank_id }}">True</label>
                                        </div>
                                        <div class="true-false-option">
                                            <input type="radio" id="false_{{ @$question->question_bank_id }}"
                                                class="common-radio answer_question_mu"
                                                name="trueOrFalse_{{ @$question->question_bank_id }}" value="F"
                                                data-question="{{ @$question->question_bank_id }}"
                                                {{ isset($submited_answer) ? ($submited_answer->user_answer == 'F' ? 'checked' : '') : '' }}>
                                            <label for="false_{{ @$question->question_bank_id }}">False</label>
                                        </div>
                                    </div>
                                @elseif($question->questionBank->type == 'MT')
                                    <div class="matching-option">
                                        @php
                                            $mt_options = json_decode(
                                                $question->questionBank->questionMu->first()->title,
                                                true,
                                            );
                                            if (isset($mt_options) && is_array($mt_options) && !empty($mt_options)) {
                                                $match_questions = array_keys($mt_options);
                                                $match_answers = array_values($mt_options);
                                                shuffle($match_answers);
                                            }
                                        @endphp
                                        @isset($match_questions)
                                            @foreach ($match_questions as $q)
                                                <div class="d-flex align-items-center mb-2">
                                                    <input type="text" name="match_questions[]" class="form-control mr-2"
                                                        required value="{{ $q }}" disabled>
                                                    <select name="match_answers[]" id="match_answers" class="form-control">
                                                        @foreach ($match_answers as $answer)
                                                            <option value="{{ $answer }}">{{ $answer }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <br>
                                            @endforeach
                                        @endisset

                                    </div>
                                @else
                                    <div class="fill-blank">
                                        <div class="row">
                                            <div class="col-10">
                                                <textarea class="form-control mt-2 form_filler_{{ @$question->question_bank_id }}"
                                                    name="answer_word_{{ @$question->question_bank_id }}" id="answer_word_{{ @$question->question_bank_id }}"
                                                    rows="3">{{ isset($submited_answer) ? $submited_answer->user_answer : '' }}</textarea>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="primary-btn fix-gr-bg mt-2"
                                                    data-question="{{ @$question->question_bank_id }}"
                                                    onclick="fillBlanks({{ @$question->question_bank_id }})">
                                                    {{ __('common.Fill') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <input type="hidden" name="marks[]"
                                    value="{{ @$question->questionBank != '' ? @$question->questionBank->id : '' }}">
                            </div>
                        @endforeach

                        @if (isset($assigned_questions) && !$assigned_questions->isEmpty())
                            <div class="text-center mt-4 mb-4">
                                <button type="submit" class="submit-btn">
                                    <i class="fas fa-check mr-2"></i>
                                    @lang('exam.submit_exam')
                                </button>
                            </div>
                        @endif
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $(document).on('change', '.answer_question_mu', function() {
            let question_id = $(this).data('question');
            let option = $(this).data('option');
            let online_exam_id = $('#online_exam_id').val();
            let submit_value = '';

            if ($(this).is(':checked')) {
                submit_value = $(this).val();
            }

            // Matching question handler
            document.querySelectorAll('[id^="match_answers"]').forEach(select => {
                select.addEventListener('change', function() {
                    const questionId = this.closest('.matching-option').dataset.questionId;
                    const matchQuestions = Array.from(this.closest('.matching-option')
                            .querySelectorAll('input[name="match_questions[]"]'))
                        .map(input => input.value);
                    const matchAnswers = Array.from(this.closest('.matching-option')
                            .querySelectorAll('select[name="match_answers[]"]'))
                        .map(select => select.value);

                    let pairs = {};
                    matchQuestions.forEach((question, index) => {
                        pairs[question] = matchAnswers[index];
                    });

                    $.ajax({
                        url: "{{ route('ajax_student_online_exam_submit') }}",
                        method: "GET",
                        data: {
                            online_exam_id: $('#online_exam_id').val(),
                            question_id: questionId,
                            submit_value: JSON.stringify(pairs)
                        },
                        success: function(result) {
                            if (result.type == 'warning') {
                                toastr.warning(result.message, result.title, {
                                    timeOut: 5000
                                });
                            }
                        }
                    });
                });
            });
        });

        function fillBlanks(question_id) {
            let online_exam_id = $('#online_exam_id').val();
            let submit_value = $('#answer_word_' + question_id).val();

            $.ajax({
                url: "{{ route('ajax_student_online_exam_submit') }}",
                method: "GET",
                data: {
                    online_exam_id: online_exam_id,
                    question_id: question_id,
                    submit_value: submit_value,
                },
                success: function(result) {
                    if (result.type == 'warning') {
                        toastr.warning(result.message, result.title, {
                            timeOut: 5000
                        });
                    } else {
                        toastr.success(result.message, result.title, {
                            timeOut: 5000
                        });
                    }
                }
            });
        }
    </script>
@endpush
