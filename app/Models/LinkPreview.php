<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LinkPreview extends Model {
    protected $fillable = ['url','title','description','image','site_name','fetched_at'];
    protected $casts = ['fetched_at'=>'datetime'];
}
