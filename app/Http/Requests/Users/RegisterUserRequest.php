<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\RequestRoot;
use App\Models\User;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends RequestRoot
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:8', 'max:30', Rules\Password::defaults()],
        ];
    }
}
