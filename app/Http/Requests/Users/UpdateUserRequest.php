<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends RequestRoot
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'photo' => 'nullable|file|mimes:jpeg,png,gif|max:2048',
            'name' => 'nullable|string|max:50',
            'email' => 'nullable|string|email',
        ];
    }
}
