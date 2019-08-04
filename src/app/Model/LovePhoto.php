<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LovePhoto extends Model
{
    protected $fillable = ['album_photo_id','love_count'];
}
