<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => ['sometimes','string','min:2','max:100'],
            'description' => ['nullable','string','max:500'],
            'is_private' => ['sometimes','boolean'],
        ];
    }
}
