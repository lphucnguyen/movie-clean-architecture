<?php

namespace App\Presentation\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
            'name' => 'required|string|max:20|min:5',
            'email' => [
                'required',
                'email',
                'string',
                Rule::unique('admins')->ignore($this->email, 'email'),
            ],
            'avatar' => ['required', 'string', function ($attribute, $value, $fail) {
                $parts = explode("/", $value);
                $file = implode('/', array_slice($parts, -2));

                if (!Storage::exists($file)) {
                    $fail(__("Avatar không tồn tại"));
                }
            }],
            'password' => 'nullable|string|confirmed|min:6',
        ];
    }
}
