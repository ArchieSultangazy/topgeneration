<?php

namespace App\Http\Requests\CL;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class CourseCommentRequest extends FormRequest
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
     * Handle a failed validation attempt.
     *
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
        $rules = [
            'body' => 'required',
        ];

        if ($this->method() == 'POST') {
            $rules['course_id'] = 'required|integer';
        }

        return $rules;
    }
}
