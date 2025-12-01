<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteUsersRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', Rule::exists(User::class, 'uuid')],
        ];
    }
}
