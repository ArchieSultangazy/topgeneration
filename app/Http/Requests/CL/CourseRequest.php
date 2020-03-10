<?php

namespace App\Http\Requests\CL;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class CourseRequest extends FormRequest
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
            'locale' => 'required|in:' . implode(',', array_keys(config('app.locales'))),
            'title' => 'required',
            //'themes' => 'required|array',
            //'authors' => 'required|array',
            'body_in' => 'required',
            'body_out' => 'required',
            'img_preview' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video' => 'mimes:mp4,avi',
        ];

        return $rules;
    }
}
