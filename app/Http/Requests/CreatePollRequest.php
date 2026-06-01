<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreatePollRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'question' => ['required','string','max:500'],
            'options' => ['required','array','min:2','max:10'],
            'options.*' => ['required','string','max:200'],
            'is_multiple_choice' => ['boolean'],
            'is_anonymous' => ['boolean'],
            'ends_at' => ['nullable','date','after:now'],
        ];
    }
}
