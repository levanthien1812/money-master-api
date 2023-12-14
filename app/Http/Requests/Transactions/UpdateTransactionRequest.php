<?php

namespace App\Http\Requests\Transactions;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class UpdateTransactionRequest extends RequestRoot
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
            'title' => 'nullable|string',
            'wallet_id' => 'nullable|numeric',
            'category_id' => 'nullable|numeric',
            'amount' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:2048',
            'is_image_cleared' => 'nullable|bool',
        ];
    }
}
