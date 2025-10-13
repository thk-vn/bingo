<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResgisterBingoUserRequest extends FormRequest
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
            'department' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.string' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('view.bingo_user.name')]),
            'department.required' => __('validation.reuqired', ['attribute' => __('view.bingo_user.department')]),
            'department.string' => __('validation.string', ['attribute' => __('view.bingo_user.department')]),
            'department.max' => __('validation.max', ['attribute' => __('view.bingo_user.department')]),
            'phone_number.required' => __('validation.reuqired', ['attribute' => __('view.bingo_user.phone_number')]),
            'phone_number.string' => __('validation.string', ['attribute' => __('view.bingo_user.phone_number')]),
        ];
    }
}
