<?php

namespace App\Http\Requests\KB;

use App\Models\KB\Article;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class ArticleRequest extends FormRequest
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
//            'is_published' => 'required',
            'type' => 'required|in:' . implode(',', array_keys(Article::getAvailableTypes())),
            'themes' => 'required|array',
            'title' => 'required|string|max:255',
            Article::TYPE_TEXT => 'required_if:type,' . Article::TYPE_TEXT,
            Article::TYPE_VIDEO_OUT => 'required_if:type,' . Article::TYPE_VIDEO_OUT,
        ];

        if ($this->method() == 'POST') {
            $rules[Article::TYPE_VIDEO_IN] = 'required_if:type,' . Article::TYPE_VIDEO_IN;
        }

        return $rules;
    }
}
