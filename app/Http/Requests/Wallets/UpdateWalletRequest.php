<?php

namespace App\Http\Requests\Wallets;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWalletRequest extends RequestRoot
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
            'name' => 'string|nullable',
            'image' => $this->getValidationRule('image'),
            'default' => 'boolean|nullable',
        ];
    }

    public function getValidationRule(String $key): string
    {
        if (request()->hasFile($key)) {
            return "nullable|file|mimes:jpeg,png,gif|max:2048";
        }
        return "nullable|string";
    }
}
