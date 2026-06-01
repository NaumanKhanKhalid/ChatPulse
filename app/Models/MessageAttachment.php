<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MessageAttachment extends Model {
    protected $fillable = ['message_id','original_name','stored_name','file_path','file_type','file_size','thumbnail_path','og_title','og_description','og_image','og_url'];
    public function message() { return $this->belongsTo(Message::class); }
    public function getUrlAttribute(): string { return asset('storage/' . $this->file_path); }
    public function isImage(): bool { return str_starts_with($this->file_type, 'image/'); }
    public function isVideo(): bool { return str_starts_with($this->file_type, 'video/'); }
    public function getFormattedSizeAttribute(): string {
        $size = $this->file_size;
        if ($size >= 1048576) return round($size/1048576, 1) . ' MB';
        if ($size >= 1024) return round($size/1024, 1) . ' KB';
        return $size . ' B';
    }
}
