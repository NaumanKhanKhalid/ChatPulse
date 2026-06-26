<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class ExportChatRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'format' => ['required','in:csv,pdf'],
            'from' => ['nullable','date'],
            'to' => ['nullable','date','after_or_equal:from'],
        ];
    }
}
