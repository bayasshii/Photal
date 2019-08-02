<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    // コントローラで使うカラムを記入
    protected $fillable = ['id','album_id','album_name','album_startDate','album_endDate'];
}
