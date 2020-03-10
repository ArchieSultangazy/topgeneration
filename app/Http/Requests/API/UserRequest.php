<?php

namespace App\Http\Requests\API;

use App\Models\AccessGroup;
use App\Models\Job\Domain;
use App\Models\Job\Specialization;
use App\Models\Location\Region;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
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
        throw new HttpResponseException(response()->json([
            'success' => false,
            'data' => [
                'errors' => $validator->errors()->getMessages()
            ]], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'username' => 'nullable|unique:users,username',
            'firstname' => 'required',
            'lastname' => 'required',

            //TODO:: make required after front-end will be updated
            'type' => 'nullable|integer|in:' . implode(',', AccessGroup::query()
                    ->where('id', '>=', 10)
                    ->pluck('id')
                    ->toArray()),
            'class_year' => 'nullable|integer|between:0,12',
            'class_form' => 'nullable|alpha|max:1',
        ];

        $regionIds = implode(',', Region::all()->pluck('id')->toArray());
        $specializationIds = implode(',', Specialization::all()->pluck('id')->toArray());
        $jobDomainIds = implode(',', Domain::all()->pluck('id')->toArray());

        switch ($this->method()) {
            case 'POST':
                {
                    $rules['phone'] = 'required|numeric|digits:11';
                    $rules['password'] = 'required';
                    $rules['c_password'] = 'required|same:password';

                    //$rules['region_id'] = 'required_if:type,==,' . \App\User::TYPE_STUDENT . '|integer';
                    //$rules['school_id'] = 'required_if:type,==,' . \App\User::TYPE_STUDENT . '|integer';
                }
            case 'PUT':
                {
                    $rules['email'] = 'nullable|email|unique:users,email,' . Auth::id();
                    $rules['avatar'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                    $rules['status'] = 'max:45';
                    $rules['about'] = 'max:240';
                    $rules['contacts'] = 'json';
                    $rules['birth_date'] = 'date';
                    $rules['region_id'] = 'nullable|integer|in:' . $regionIds;

                    $rules['specializations'] = 'json';
                    $rules['specializations.*'] = 'nullable|integer|in' . $specializationIds;

                    $rules['job'] = 'array';
                    $rules['job.domain_id'] = 'nullable|integer|in:' . $jobDomainIds;

                    //$rules['school_id'] = 'required_if:type,==,' . \App\User::TYPE_STUDENT . '|integer';
                }
        }

        return $rules;
    }
}
