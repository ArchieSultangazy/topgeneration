<?php

namespace App\Http\Requests\CL;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class LessonRequest extends FormRequest
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
            'position' => 'numeric',
            'course_id' => 'required|numeric',
            'title' => 'required',
            'body' => 'required',
            'scheme' => 'json',
            'files' => 'array',
            'articles' => 'json',
        ];

        if (!Request::wantsJson()) {
            unset($rules['articles']);
        }

        switch ($this->method()) {
            case 'POST':
                {
                    $rules['img_preview'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                    $rules['video'] = 'sometimes|required|mimes:mp4,avi';
                    break;
                }
            case 'PUT':
                {
                    $rules['img_preview'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                    $rules['video'] = 'mimes:mp4,avi';
                    break;
                }
        }

        return $rules;
    }
}
