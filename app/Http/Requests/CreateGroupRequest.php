<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => ['required','string','min:2','max:100'],
            'description' => ['nullable','string','max:500'],
            'is_private' => ['boolean'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer','exists:users,id'],
        ];
    }
}
