<?php

namespace App\Http\Requests\CL;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
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
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        if (Request::wantsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors()->getMessages()
                ]], 422));
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
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
        ];
    }

    public function mutatorData()
    {
        return $this->except('_token');
    }
}
