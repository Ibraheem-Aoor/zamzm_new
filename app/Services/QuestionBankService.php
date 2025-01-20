<?php

namespace App\Services;

use App\Http\Requests\Admin\OnlineExam\SmQuestionBankRequest;
use App\SmQuestionBank;
use App\SmQuestionBankMuOption;
use App\SmGeneralSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Brian2694\Toastr\Facades\Toastr;

class QuestionBankService
{
    private $allowedImageMimes = ['image/png', 'image/jpg', 'image/jpeg'];
    private $uploadPath = 'public/uploads/upload_contents/';

    public function store(SmQuestionBankRequest $request)
    {
        try {
            DB::beginTransaction();

            switch ($request->question_type) {
                case 'M': // Multiple Choice
                    $result = $this->handleMultipleChoiceQuestion($request);
                    break;
                case 'MI': // Multiple Choice with Images
                    $result = $this->handleImageQuestion($request);
                    break;
                case 'VI': // Video Question
                    $result = $this->handleVideoQuestion($request);
                    break;
                case 'MT': // Video Question
                    $result = $this->handleMatchQuestion($request);
                    break;
                default: // Basic, Fill in blanks(F), True/False(T)
                    $result = $this->handleBasicQuestion($request);
            }

            DB::commit();
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return $this->handleError($e);
        }
    }

    public function update(SmQuestionBankRequest $request, $id)
    {

        try {
            DB::beginTransaction();

            switch ($request->question_type) {
                case 'M':
                    $result = $this->updateMultipleChoiceQuestion($request, $id);
                    break;
                case 'MI':
                    $result = $this->updateImageQuestion($request, $id);
                    break;
                case 'VI':
                    $result = $this->updateVideoQuestion($request, $id);
                    break;
                case 'MT':
                    $result = $this->updateMatchQuestion($request, $id);
                    break;
                default:
                    $result = $this->updateBasicQuestion($request, $id);
            }

            DB::commit();
            return $this->handleResponse($result, true);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return $this->handleError($e);
        }
    }

    private function handleMultipleChoiceQuestion($request)
    {
        foreach ($request->section as $section) {
            $question = $this->createBaseQuestion($request, $section);
            $question->number_of_option = $request->number_of_option;
            $question->save();

            if (isset($request->option)) {
                $this->saveMultipleChoiceOptions($request, $question);
            }
        }
        return true;
    }
    private function handleMatchQuestion($request)
    {

        $question = $this->createBaseQuestion($request, $request->section[0]);
        $question->save();
        $answers = $request->match_answers;
        foreach ($request->match_questions as $index => $match_question) {
            $title[$match_question] = $answers[$index];
        }
        $this->saveOption($question->id, json_encode($title , JSON_UNESCAPED_UNICODE), true);
        return true;
    }


    private function updateMultipleChoiceQuestion($request, $id)
    {
        $question = SmQuestionBank::findOrFail($id);
        $this->updateBaseQuestion($question, $request);
        $question->number_of_option = $request->number_of_option;
        $question->save();

        if (isset($request->option)) {
            SmQuestionBankMuOption::where('question_bank_id', $question->id)->delete();
            $this->saveMultipleChoiceOptions($request, $question);
        }

        return true;
    }
    private function updateMatchQuestion($request, $id)
    {
        $question = SmQuestionBank::findOrFail($id);
        $this->updateBaseQuestion($question, $request);
        $question->save();
        $answers = $request->match_answers;
        foreach ($request->match_questions as $index => $match_question) {
            $title[$match_question] = $answers[$index];
        }
        SmQuestionBankMuOption::query()->where('question_bank_id' , $question->id)->update([
            'title' => $title
        ]);
        return true;
    }

    private function handleBasicQuestion($request)
    {
        foreach ($request->section as $section) {
            $question = $this->createBaseQuestion($request, $section);

            if ($request->question_type == "F") {
                $question->suitable_words = $request->suitable_words;
            } elseif ($request->question_type == "T") {
                $question->trueFalse = $request->trueOrFalse;
            }

            $question->save();
        }
        return true;
    }

    private function handleImageQuestion($request)
    {
        $this->ensureImageQuestionSchema();
        $fileName = $this->handleImageUpload($request->file('question_image'));

        foreach ($request->section as $section) {
            $question = $this->createBaseQuestion($request, $section);
            $question->question_image = $fileName;
            $question->answer_type = $request->answer_type;
            $question->number_of_option = $request->number_of_optionImg ?? $request->number_of_option;
            $question->save();

            if (isset($request->images)) {
                $this->saveImageOptions($request, $question);
            }
        }
        return true;
    }

    private function handleVideoQuestion($request)
    {
        $this->ensureVideoQuestionSchema();
        $videoFileName = $this->handleVideoUpload($request->file('question_video'));

        foreach ($request->section as $section) {
            $question = $this->createBaseQuestion($request, $section);
            $question->question_video = $videoFileName;
            $question->number_of_option = $request->number_of_option;
            $question->save();

            if (isset($request->option)) {
                $this->saveMultipleChoiceOptions($request, $question);
            }
        }
        return true;
    }

    private function createBaseQuestion($request, $section)
    {
        $question = new SmQuestionBank();
        $this->fillBaseQuestionData($question, $request, $section);
        return $question;
    }

    private function updateBaseQuestion($question, $request)
    {
        $this->fillBaseQuestionData($question, $request, $request->section);
    }

    private function fillBaseQuestionData($question, $request, $section)
    {
        $question->type = $request->question_type;
        $question->q_group_id = $request->group;
        $question->class_id = $request->class;
        $question->section_id = $section;
        $question->marks = $request->marks;
        $question->question = $request->question;
        $question->school_id = Auth::user()->school_id;
        $question->academic_id = getAcademicId();
    }

    private function handleImageUpload($file)
    {
        if (!$file)
            return null;

        $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
        $fileSizeKb = filesize($file) / 1000000;

        if ($fileSizeKb >= $maxFileSize) {
            throw new \Exception("Max upload file size {$maxFileSize} Mb is set in system");
        }

        if (!in_array($file->getMimeType(), $this->allowedImageMimes)) {
            throw new \Exception("Invalid file type");
        }

        $image_info = getimagesize($file);
        if ($image_info[0] > 650 || $image_info[1] > 450) {
            throw new \Exception("Question Image should be 650x450");
        }

        return $this->uploadFile($file);
    }

    private function handleVideoUpload($file)
    {
        if (!$file)
            return null;
        return $this->uploadFile($file);
    }

    private function uploadFile($file)
    {
        $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
        $file->move($this->uploadPath, $fileName);
        return $this->uploadPath . $fileName;
    }

    private function ensureImageQuestionSchema()
    {
        if (!Schema::hasColumn('sm_question_banks', 'question_image')) {
            Schema::table('sm_question_banks', function ($table) {
                $table->string('question_image')->nullable();
            });
        }
        if (!Schema::hasColumn('sm_question_banks', 'answer_type')) {
            Schema::table('sm_question_banks', function ($table) {
                $table->string('answer_type')->nullable();
            });
        }
    }

    private function ensureVideoQuestionSchema()
    {
        if (!Schema::hasColumn('sm_question_banks', 'question_video')) {
            Schema::table('sm_question_banks', function ($table) {
                $table->mediumText('question_video')->nullable();
            });
        }
    }

    private function saveMultipleChoiceOptions($request, $question)
    {
        foreach ($request->option as $key => $option) {
            $optionCheck = 'option_check_' . ($key + 1);
            $this->saveOption($question->id, $option, $request->$optionCheck ?? false);
        }
    }

    private function saveImageOptions($request, $question)
    {
        foreach ($request->images as $key => $image) {
            $optionCheck = 'option_check_' . ($key + 1);
            $fileName = $this->handleImageUpload($request->file('images')[$key]);
            $this->saveOption($question->id, $fileName, $request->$optionCheck ?? false);
        }
    }

    private function saveOption($questionId, $title, $isCorrect)
    {
        $option = new SmQuestionBankMuOption();
        $option->question_bank_id = $questionId;
        $option->title = $title;
        $option->status = $isCorrect ? 1 : 0;
        $option->school_id = Auth::user()->school_id;
        $option->academic_id = getAcademicId();
        $option->save();
    }

    private function handleResponse($result, $isUpdate = false)
    {
        $status = false;
        $message = 'Operation Failed';
        $redirect = route('question-bank');
        if ($result) {
            $status = true;
            $message = 'Operation successful';
            $redirect = route('question-bank');
        }
        return response()->json(['status' => $status, 'message' => $message, 'redirect' => $redirect]);
    }

    private function handleError(\Exception $e)
    {
        \Log::error('QuestionBank Error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Operation Failed', 'redirect' => route('question-bank')]);
    }

    private function updateBasicQuestion($request, $id)
    {
        $question = SmQuestionBank::findOrFail($id);
        $this->updateBaseQuestion($question, $request);

        if ($request->question_type == "F") {
            $question->suitable_words = $request->suitable_words;
        } elseif ($request->question_type == "T") {
            $question->trueFalse = $request->trueOrFalse;
        }

        return $question->save();
    }

    private function updateImageQuestion($request, $id)
    {
        $this->ensureImageQuestionSchema();
        $question = SmQuestionBank::findOrFail($id);
        $this->updateBaseQuestion($question, $request);

        // Handle question image
        if ($request->hasFile('question_image')) {
            $fileName = $this->handleImageUpload($request->file('question_image'));
            $question->question_image = $fileName;
        }

        $question->answer_type = $request->answer_type;
        $question->number_of_option = $request->number_of_optionImg ?? $request->number_of_option;
        $question->save();

        $i = 0;
        // Handle options update
        if (isset($request->images_old)) {
            SmQuestionBankMuOption::where('question_bank_id', $question->id)->delete();

            foreach ($request->images_old as $key => $oldImage) {
                $i++;
                $optionCheck = 'option_check_' . $i;

                // Determine the image to use (new upload or existing)
                if (isset($request->images[$key])) {
                    $optionImage = $request->file('images')[$key];
                    $fileName = $this->handleImageUpload($optionImage);
                } else {
                    $fileName = $oldImage;
                }

                $this->saveOption($question->id, $fileName, $request->$optionCheck ?? false);
            }
        }

        return true;
    }

    private function updateVideoQuestion($request, $id)
    {
        $this->ensureVideoQuestionSchema();
        $question = SmQuestionBank::findOrFail($id);
        $this->updateBaseQuestion($question, $request);

        // Handle video upload if new video is provided
        if ($request->hasFile('question_video')) {
            $videoFileName = $this->handleVideoUpload($request->file('question_video'));
            $question->question_video = $videoFileName;
        }

        $question->number_of_option = $request->number_of_option;
        $question->save();

        // Update multiple choice options
        if (isset($request->option)) {
            SmQuestionBankMuOption::where('question_bank_id', $question->id)->delete();
            $this->saveMultipleChoiceOptions($request, $question);
        }

        return true;
    }

    private function deleteOldFile($filePath)
    {
        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }
    }
}