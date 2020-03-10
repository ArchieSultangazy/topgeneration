<?php

namespace App\Http\Requests\API\CL\Moderation;

use App\Models\CL\Test;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class QuestionRequest extends FormRequest
{
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
        if (!$lessonTestID = $this->input('lesson_test_id', null)) {
            $e = ValidationException::withMessages(['lesson_test_id is not set']);
            throw $e;
        }

        if (!Test::find($lessonTestID)) {
            $e = ValidationException::withMessages(['Test doesnt exist']);
            throw $e;
        }

        return [
            'ru_name' => ['required'],
        ];
    }

    public function mutatorData()
    {
        return $this->all();
    }
}
