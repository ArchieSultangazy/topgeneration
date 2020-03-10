<?php

namespace App\Http\Requests\API\CL\Moderation;

use App\Models\CL\TestQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AnswerRequest extends FormRequest
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
        if (!$questionTestID = $this->input('question_id', null)) {
            $e = ValidationException::withMessages(['question_id is not set']);
            throw $e;
        }

        if (!TestQuestion::find($questionTestID)) {
            $e = ValidationException::withMessages(['Question doesnt exist']);
            throw $e;
        }

        return [
            'ru_name' => ['required'],
            'is_correct' => ['required', 'integer']
        ];
    }

    public function mutatorData()
    {
        return $this->all();
    }
}
