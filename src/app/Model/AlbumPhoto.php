<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AlbumPhoto extends Model
{
    // コントローラで使うカラムを記入
    protected $fillable = ['album_id','album_photo'];
}
