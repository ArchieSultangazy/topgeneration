<?php

namespace App\Http\Requests\CL;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'ru_name' => 'required|string',
            'kk_name' => 'required|string',
            'en_name' => 'required|string',
            'is_correct' => 'bool',
        ];
    }

    public function mutatorData()
    {
        return $this->except('_token');
    }
}
