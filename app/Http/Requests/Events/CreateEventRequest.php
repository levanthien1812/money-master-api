<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateEventRequest extends FormRequest
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
            'name' => 'required|string',
            'date_begin' => 'required|date',
            'date_end' => 'required|date',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'wallet_id' => 'required|numeric'
        ];
    }
}
