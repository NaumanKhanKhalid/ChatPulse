<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'status_type' => ['required','in:available,busy,away'],
            'status_message' => ['nullable','string','max:60'],
            'status_emoji' => ['nullable','string','max:10'],
            'clear_after' => ['nullable','in:1hour,4hours,today,week'],
        ];
    }
}
