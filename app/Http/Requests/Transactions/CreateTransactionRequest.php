<?php

namespace App\Http\Requests\Transactions;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class CreateTransactionRequest extends RequestRoot
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
            'title' => 'string|required',
            'wallet_id' => 'numeric|required',
            'category_id' => 'numeric|required',
            'amount' => 'numeric|required|min:0',
            'date' => 'date|required',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,gif|max:2048',
        ];
    }
}
