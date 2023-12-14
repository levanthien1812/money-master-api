<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\RequestRoot;

class ResetPasswordRequest extends RequestRoot
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'newPassword' => 'required|string|confirmed|min:8',
            'token' => 'required|string',
            'email' => 'required|email',
        ];
    }
}
