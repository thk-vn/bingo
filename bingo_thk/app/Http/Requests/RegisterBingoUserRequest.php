<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterBingoUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('bingo_users', 'email')],
            'phone_number' => ['required', 'string', 'max:255', Rule::unique('bingo_users', 'phone_number')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.string' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('view.bingo_user.name')]),
            'email.required' => __('validation.reuqired', ['attribute' => __('view.bingo_user.email')]),
            'email.email' => __('validation.email', ['attribute' => __('view.bingo_user.email')]),
            'phone_number.required' => __('validation.reuqired', ['attribute' => __('view.bingo_user.phone_number')]),
            'phone_number.string' => __('validation.string', ['attribute' => __('view.bingo_user.phone_number')]),
        ];
    }
}
