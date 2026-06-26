<?php
namespace App\Jobs;

use App\Models\MessageAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessFileUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public function backoff(): array { return [10, 30, 60]; }

    public function __construct(public MessageAttachment $attachment) {}

    public function handle(): void
    {
        // Generate thumbnail for images if GD is available
        if (!$this->attachment->isImage()) return;
        if (!function_exists('imagecreatefromstring')) return;

        $sourcePath = storage_path('app/public/' . $this->attachment->file_path);
        if (!file_exists($sourcePath)) return;

        $image = @imagecreatefromstring(file_get_contents($sourcePath));
        if (!$image) return;

        $thumbWidth = 320;
        $thumbHeight = 200;
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        $ratio = min($thumbWidth / $origWidth, $thumbHeight / $origHeight);
        $newW = (int)($origWidth * $ratio);
        $newH = (int)($origHeight * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newW, $newH, $origWidth, $origHeight);

        $thumbPath = 'thumbnails/' . pathinfo($this->attachment->stored_name, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbFullPath = storage_path('app/public/' . $thumbPath);
        @mkdir(dirname($thumbFullPath), 0755, true);
        imagejpeg($thumb, $thumbFullPath, 80);
        imagedestroy($image);
        imagedestroy($thumb);

        $this->attachment->update(['thumbnail_path' => $thumbPath]);
    }

    public function failed(Throwable $e): void
    {
        \Log::warning('ProcessFileUploadJob failed', ['attachment_id' => $this->attachment->id, 'error' => $e->getMessage()]);
    }
}
