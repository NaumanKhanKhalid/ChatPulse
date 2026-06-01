<?php
namespace App\Services;

use App\Models\MessageAttachment;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function store(UploadedFile $file, Message $message): MessageAttachment
    {
        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $storedName = Str::uuid() . '_' . $sanitizedName;
        $path = $file->storeAs('attachments/' . date('Y/m'), $storedName, 'public');

        return MessageAttachment::create([
            'message_id' => $message->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function storeAvatar(UploadedFile $file, int $userId): string
    {
        $storedName = 'avatars/' . $userId . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('', $storedName, 'public');
        return $storedName;
    }
}
