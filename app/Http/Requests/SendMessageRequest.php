<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'body' => ['nullable','string','max:10000'],
            'type' => ['sometimes','in:text,image,file,poll,forwarded'],
            'parent_id' => ['nullable','integer','exists:messages,id'],
            'scheduled_at' => ['nullable','date','after:now'],
            'attachments.*' => ['file','max:51200'], // 50MB max
        ];
    }
}
