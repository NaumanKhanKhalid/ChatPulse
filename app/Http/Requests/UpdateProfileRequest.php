<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => ['sometimes','string','min:2','max:100'],
            'bio' => ['nullable','string','max:160'],
            'avatar' => ['nullable','image','max:2048'],
        ];
    }
}
