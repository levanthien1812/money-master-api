<?php

namespace App\Http\Requests\Plans;

use App\Http\Requests\RequestRoot;
use Illuminate\Support\Facades\Auth;

class UpdatePlanRequest extends RequestRoot
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
            'amount' => 'numeric|required',
        ];
    }
}
