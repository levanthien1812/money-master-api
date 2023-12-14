<?php

namespace App\Http\Requests\GoalAdditions;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class CreateGoalAdditionRequest extends RequestRoot
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'wallet_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
        ];
    }
}
