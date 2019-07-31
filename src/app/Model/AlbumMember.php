<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AlbumMember extends Model
{
    // コントローラで使うカラムを記入
    protected $fillable = ['album_id','album_member'];
}
