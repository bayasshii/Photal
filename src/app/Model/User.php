<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // コントローラで使うカラムを記入
    protected $fillable = ['id','name','comment','github_id'];
}
