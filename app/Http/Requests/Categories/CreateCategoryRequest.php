<?php

namespace App\Http\Requests\Categories;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class CreateCategoryRequest extends RequestRoot
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
            'name' => 'string|required',
            'image' => 'file|required|mimes:jpeg,png,gif',
            'type' => 'numeric|required',
        ];
    }
}
