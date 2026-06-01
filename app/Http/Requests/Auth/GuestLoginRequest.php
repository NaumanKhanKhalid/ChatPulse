<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;

class GuestLoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => ['required','string','min:2','max:60'],
            'website' => ['max:0'], // honeypot - must be empty
        ];
    }
    public function messages(): array {
        return ['website.max' => 'Bot detected.'];
    }
}
