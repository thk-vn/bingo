<?php

namespace App\Http\Requests;

use App\Rules\EmailRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBingoUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
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
            'email' => ['required', new EmailRule()],
            'phone_number' => ['required', new PhoneRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.string' => __('validation.required', ['attribute' => __('view.bingo_user.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('view.bingo_user.name')]),
            'email.required' => __('validation.required', ['attribute' => __('view.bingo_user.email')]),
            'phone_number.required' => __('validation.required', ['attribute' => __('view.bingo_user.phone_number')]),
        ];
    }
}
