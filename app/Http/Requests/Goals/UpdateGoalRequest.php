<?php

namespace App\Http\Requests\Goals;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class UpdateGoalRequest extends RequestRoot
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
            'type' => 'nullable|numeric|in:saving,debt-reduction',
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'date_begin' => 'nullable|date',
            'date_end' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'is_important' => 'nullable',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'status' => 'required|string',
        ];
    }
}
