<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\RequestRoot;
use App\Models\User;

class SendVerficationCodeRequest extends RequestRoot
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
            'email' => ['required', 'string', 'email', 'unique:'.User::class],
        ];
    }
}
