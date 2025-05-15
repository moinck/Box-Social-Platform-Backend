<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string',
            'fca_no' => 'required|numeric|min:6',
            'website' => 'required|string|url',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'company_name.required' => 'Company name is required',
            'fca_no.required' => 'FCA number is required',
            'fca_no.numeric' => 'FCA number must be a number',
            'fca_no.min' => 'FCA number must be at least 6 characters',
            'website.required' => 'Website is required',
            'website.url' => 'Please provide a valid website URL',
        ];
    }
}
