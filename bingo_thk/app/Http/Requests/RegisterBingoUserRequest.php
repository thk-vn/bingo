<?php

namespace App\Http\Requests;

use App\Rules\EmailRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', new EmailRule()],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.string' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('view.bingo_user.name')]),
            'email.required' => __('validation.required', ['attribute' => __('view.bingo_user.email')]),
        ];
    }
}
